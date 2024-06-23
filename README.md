<p align="center">
    <img width="636" alt="practice scale logo" src="assets/img/practice-scale_logo.png">
</p>

<h3 align="center">
    Команда <censored>
</h3>
<p align="center">
    Хакатон <censored> от <censored>
</p> 

Помимо реализации алгоритма обработки звонка, была разработана полноценная система оценки качества звонков и помощи операторам при взаимодействии с клиентами. Сайт доступен по [psai.tw1.su](https://psai.tw1.su/)

---
## Инструкция по установке и использованию скрипта
### Клонировать проект
`git clone https://github.com/ItsHimReally/InCallytics.git`
### Установить зависимости
Перейти в папку script_files и установить зависимости  
`cd InCallytics/script_files && pip install -r requirements.txt`
### Установить модель
Скачать веса модели rubert-2 ([ссылка](https://drive.google.com/file/d/1WEIKdmmZLRExeU8Mr2JjP-6HS6UBpY_x/view)), поместить в одну с директорию файлами `script.py` и `models.py`
### Запуск скрипта
##### Предсказание
- `python script.py --dataset ./path_to_dataset --classifier_model ./path_to_model`,  
где `./path_to_model` - путь к модели скачаной в 3 пункте, по умолчанию считается, что модель находится в директории script_files

##### Обучение
Перед запуском в директории `script_files` нужно создать папку `models` для сохранения чекпоинтов моделей. Также для обучения необходим файл `targets.csv`, который нужно поместить в папку `script_files`. В нем должны присутствовать колонока `target` (бин. занчение 0,1 для классов, и колонка id для соотнесения фалов в директории датасета с разметкой)
- `python script.py --learn --dataset ./path_to_dataset`  
