import os

IAM_TOKEN=os.environ.get("IAM_TOKEN")
FOLDER_ID=os.environ.get("FOLDER_ID")

MODEL_TYPE = "yandexgpt"
COMPLETION_URL: str = "https://llm.api.cloud.yandex.net/foundationModels/v1/completionAsync"
HEADERS = {
            "Content-Type": "application/json",
            "Authorization": f"Bearer {IAM_TOKEN}",
            "x-folder-id": FOLDER_ID
        }

HG_AUTH_TOKEN=os.environ.get("HG_AUTH_TOKEN")