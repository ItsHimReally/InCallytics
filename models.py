import torch
import torchmetrics
import transformers
from transformers import AutoTokenizer, AutoModelForSequenceClassification
from transformers import AutoModelForSpeechSeq2Seq, AutoProcessor, pipeline
from torch.utils.data import Dataset, DataLoader, random_split

class textDataset(Dataset):
    def __init__(self, texts, targets, tokenizer, max_len=2048):
        self.texts = texts
        self.targets = targets
        self.tokenizer = tokenizer
        self.max_len = max_len

    def __len__(self):
        return len(self.targets)

    def __getitem__(self, idx):
        text = str(self.texts[idx])
        target = self.targets[idx]

        encoding = self.tokenizer.encode_plus(
            text,
            add_special_tokens=True,
            max_length=self.max_len,
            return_token_type_ids=False,
            padding='max_length',
            return_attention_mask=True,
            return_tensors='pt',
            truncation=True
        )

        return {
            'text': text,
            'input_ids': torch.tensor(encoding['input_ids']).flatten(),
            'attention_mask': torch.tensor(encoding['attention_mask']).flatten(),
            'targets': torch.tensor(target, dtype=torch.long)
        }


class CustomTextClassifier:
    def __init__(self, model_path, tokenizer_path, n_classes=2, models_save_path='/content/best.pt'):
        self.model = AutoModelForSequenceClassification.from_pretrained(model_path)
        self.tokenizer = AutoTokenizer.from_pretrained(tokenizer_path)

        self.device = torch.device("cuda" if torch.cuda.is_available() else "cpu")
        self.models_save_path = models_save_path
        self.max_len = 2048

        self.out_features = self.model.bert.encoder.layer[1].output.dense.out_features
        self.model.classifier = torch.nn.Linear(self.out_features, n_classes)

        self.model.to(self.device)

    def init_helpers(self, texts, targets, lr, report_step=250, train_val_test=[0.90, 0.10, 0], batch_size=64, dataset_class=textDataset):
        dataset = dataset_class(texts, targets, self.tokenizer)

        self.train_data, self.val_data, self.test_data = random_split(dataset, train_val_test)

        self.val_loader = DataLoader(self.val_data, batch_size=batch_size, shuffle=True)
        self.train_loader = DataLoader(self.train_data, batch_size=batch_size, shuffle=True)

        self.report_step = report_step

        self.loss_func = torch.nn.CrossEntropyLoss()
        self.optimizer = Adam(self.model.parameters(), lr)
        self.lr_scheduler = lr_scheduler.LinearLR(self.optimizer)

    def eval(self):
        self.model = self.model.train()
        losses = []
        correct_predicts = 0
        f1_scores = []
        f1 = torchmetrics.F1Score(task="multiclass", num_classes=3)

        with torch.no_grad():
            for batch in self.val_loader:
                input_ids = batch["input_ids"].to(self.device)
                attention_mask = batch["attention_mask"].to(self.device)
                targets = batch["targets"].to(self.device)

                outputs = self.model(
                    input_ids=input_ids,
                    attention_mask=attention_mask
                )

                preds = torch.argmax(outputs.logits, dim=1)
                loss = self.loss_func(outputs.logits, targets)

                f1_scores.append(f1(preds.cpu(), targets.cpu()))

                correct_predicts += torch.sum(preds == targets)
                losses.append(loss.item())

        val_acc = correct_predicts / len(self.val_data)
        val_loss = np.mean(losses)
        val_f1 = np.mean(f1_scores)
        return val_acc, val_loss, val_f1

    def train_one_epoch(self):
        self.model = self.model.train()
        losses = []
        correct_predicts = 0


        report_counter = 0

        for batch in self.train_loader:
            input_ids = batch["input_ids"].to(self.device)
            attention_mask = batch["attention_mask"].to(self.device)
            targets = batch["targets"].to(self.device)

            outputs = self.model(
                input_ids=input_ids,
                attention_mask=attention_mask
            )

            preds = torch.argmax(outputs.logits, dim=1)
            loss = self.loss_func(outputs.logits, targets)


            correct_predicts += torch.sum(preds == targets)
            losses.append(loss.item())

            loss.backward()
            self.optimizer.step()
            self.optimizer.zero_grad()
            self.progress_bar.update(1)

            if report_counter % self.report_step == 0:
                val_acc, val_loss, val_f1 = self.eval()
                if self.best_f1 < val_f1:
                    torch.save(self.model, f"{self.models_save_path}/{val_f1:.3f}_rubert2.pt")
                    self.best_f1 = val_f1

                print('Val_f1:', val_f1, 'Val_loss:', val_loss)

            report_counter += 1


        self.lr_scheduler.step()



    def train(self, epochs):
        self.progress_bar = tqdm(range(len(self.train_loader)*epochs))
        self.best_f1 = 0
        for epoch in range(epochs):
            self.train_one_epoch()

    def predict(self, text, ind_to_labels):
        encoding = self.tokenizer.encode_plus(
            text,
            add_special_tokens=True,
            max_length=self.max_len,
            return_token_type_ids=False,
            truncation=True,
            padding='max_length',
            return_attention_mask=True,
            return_tensors='pt',
        )

        out = {
            'text': text,
            'input_ids': encoding['input_ids'].flatten(),
            'attention_mask': encoding['attention_mask'].flatten()
        }

        input_ids = out["input_ids"].to(self.device)
        attention_mask = out["attention_mask"].to(self.device)

        outputs = self.model(
            input_ids=input_ids.unsqueeze(0),
            attention_mask=attention_mask.unsqueeze(0)
        )

        prediction = torch.argmax(outputs.logits, dim=1).cpu().numpy()[0]

        return ind_to_labels[prediction]