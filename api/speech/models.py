from transformers import AutoProcessor, pipeline
from pyannote.audio import Pipeline
import torch
from settings import HG_AUTH_TOKEN


device = "cuda:0" if torch.cuda.is_available() else "cpu"
torch_dtype = torch.float16 if torch.cuda.is_available() else torch.float32

speech_to_text_model = torch.load('speech/models/whisper_small_all.pt', map_location=torch.device('cpu'))
speech_to_text_processor = AutoProcessor.from_pretrained('openai/whisper-small')

speech_to_text_model.to(device)
speech_to_text_model.eval()

speech_to_text_pipe = pipeline(
    "automatic-speech-recognition",
    model=speech_to_text_model,
    tokenizer=speech_to_text_processor.tokenizer,
    feature_extractor=speech_to_text_processor.feature_extractor,
    max_new_tokens=128,
    chunk_length_s=30,
    batch_size=16,
    return_timestamps=True,
    torch_dtype=torch_dtype,
    device=device,
)

pyannote_pipe = Pipeline.from_pretrained(
    "pyannote/speaker-diarization-3.1",
    use_auth_token=HG_AUTH_TOKEN)

def check():
    res = speech_to_text_pipe('Phone_ARU_OFF.wav', return_timestamps=True,
                              generate_kwargs={"language": "russian"})

    diarization = pyannote_pipe('Phone_ARU_OFF.wav')

    res['diarization'] = [[speaker, turn.start, turn.end] for turn, _, speaker in diarization.itertracks(yield_label=True)]

    return res
