import speech.models
import os
from flask import Flask, jsonify, request
from yandex_gpt import YandexGPT
from settings import *

app = Flask(__name__)


@app.route('/speech_to_text', methods=['POST', 'GET'])
def speech_to_text():
    if request.method == "POST":
        is_diarization = request.args.get('diarization')
        audio = request.files['audio']

        save_path = os.path.join('speech/files/', audio.filename)

        audio.save(save_path)
        result = speech.models.speech_to_text_pipe(save_path, return_timestamps=True, generate_kwargs={"language": "russian"})

        if is_diarization == '1':
            diarization = speech.models.pyannote_pipe(save_path)

            result['diarization'] = [[speaker, turn.start, turn.end] for turn, _, speaker in
                                     diarization.itertracks(yield_label=True)]
    else:
        result = speech.models.check()

    return jsonify(result)


@app.route('/send_completion', methods=['POST'])
async def send_completion():
    request_json = request.json
    prompt_message = request_json['prompt']
    messages = [
            {
                "role": "system",
                "text": "Как профессионал в суммаризации звонков, создай ёмкую сводку по телефонному звонку. Опирайся только на информацию из телефонных звонков. Умещай ответ в единую строку, без переноса."
            },
            {
                "role": "user", "text": prompt_message
            },
        ]

    payload = {
            "modelUri": f"gpt://{FOLDER_ID}/{MODEL_TYPE}/latest",
            "completionOptions": {
                "stream": False,
                "temperature": 0.6,
                "maxTokens": 1000
            },
            "messages": messages
        }

    completion_request_id: str = await YandexGPT.send_async_completion_request(
        headers=HEADERS,
        payload=payload,
        completion_url=COMPLETION_URL
    )

    result = {"completion_id": completion_request_id}

    return jsonify(result)


@app.route('/poll_completion', methods=['POST'])
async def poll_completion():
    request_json = request.json
    operation_id = request_json['operation_id']

    completion_poll: str = await YandexGPT.poll_async_completion(
        operation_id=operation_id,
        headers=HEADERS,
        timeout=5,
    )

    result = completion_poll

    return jsonify(result)


if __name__ == '__main__':
    app.run(
        host='0.0.0.0',
        debug=True
        )
