import argparse

from tqdm.auto import tqdm
import pandas as pd
import numpy as np
import os

from models import *


def main():
    parser = argparse.ArgumentParser(description='Script for learning and processing dataset')
    
    parser.add_argument('--learn', action='store_true', help='Flag to enable learning mode')
    parser.add_argument('--targets', type=str, help='Path to the targets file, required if --learn is set')
    parser.add_argument('--dataset', type=str, required=True, help='Path to the dataset')
    parser.add_argument('--bert_path', type=str, required=True, help='Path to the model')
    
    args = parser.parse_args()

    if args.learn and not args.targets:
        parser.error('--targets is required when --learn is set')
    
    learn_flag = args.learn
    data_dir = args.dataset
    bert_path = args.bert_path
    
    device = "cuda:0" if torch.cuda.is_available() else "cpu"

    torch_dtype = torch.float16 if torch.cuda.is_available() else torch.float32

    model_id = "openai/whisper-large-v3"

    print('\nУСТАНОВКА ВЕСОВ МОДЕЛИ WHISPER\n')
    model = AutoModelForSpeechSeq2Seq.from_pretrained(
        model_id, torch_dtype=torch_dtype, use_safetensors=True
    )
    model.to(device)

    processor = AutoProcessor.from_pretrained(model_id)

    pipe = pipeline(
        "automatic-speech-recognition",
        model=model,
        tokenizer=processor.tokenizer,
        feature_extractor=processor.feature_extractor,
        max_new_tokens=128,
        chunk_length_s=30,
        batch_size=16,
        return_timestamps=True,
        torch_dtype=torch_dtype,
        device=device,
    )

    files = [i[:-4] for i in os.listdir(data_dir) if i[-4:] == '.wav']

    not_exist = []

    transcrs = []
    print('\nSTART TRANSCRIPTION\n')
    for path in tqdm(files):
        try:
            res = pipe(os.path.join(data_dir, f"{path}.wav"), generate_kwargs={"language": "russian"})
            transcrs.append({
                'id': path,
                'text': res['text']
            })
        except:
            not_exist.append(path)


    transcrs = pd.DataFrame.from_dict(transcrs)
    print('\nTRANSCRIPTION SUCCESSFUL\n')


    if learn_flag:
        print('\nSTART TRAINIG\n')
        data = pd.read_csv('targets.csv')
        texts, targets = data['text'], data['target']

        classifier = CustomTextClassifier('cointegrated/rubert-tiny2', 'cointegrated/rubert-tiny2', n_classes=2, models_save_path='./models/')
        classifier.init_helpers(texts, targets, lr, batch_size=4, train_val_test=[0.90, 0.1, 0], report_step=100)

        num_epochs = 15
        lr = 3e-4

        classifier.train(num_epochs)

        print('Training successful, models has been saved at ./models')
    else:
        print('\nУСТАНОВКА ВЕСОВ МОДЕЛИ КЛАССИФИКАТОРА\n')
        classifier = CustomTextClassifier('cointegrated/rubert-tiny2', 'cointegrated/rubert-tiny2', n_classes=2, models_save_path='./models/')

        classifier.model.classifier = torch.nn.Sequential(
            torch.nn.Linear(312, 512),
            torch.nn.Linear(512, 128),
            torch.nn.Linear(128, 2)
        )
        classifier.model.to(classifier.device);

        classifier.model = torch.load(bert_path)

        id2label = {
            0: 'False',
            1: 'True'
        }

        ans_row = ''

        print('\nSTART PREDICT')
        for row in tqdm(transcrs.values.tolist()):
            text = row[1]
            try:
                pred = classifier.predict(text, id2label)
            except:
                not_exist.append(row[0])

            ans_row += f"{row[0]}.wav-{pred}\n"

        for i in not_exist:
            ans_row += f"{i}.wav-Fail\n"

        with open('result.txt', 'w') as f:
            f.write(ans_row.strip())

        print(f'\nPREDICT WAS SUCCESSFUL, THE MODELS WERE SAVED AT {os.getcwd()}/result.txt')
    


if __name__ == '__main__':
    main()