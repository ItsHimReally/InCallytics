<p align="center">
    <img width="636" alt="practice scale logo" src="assets/img/practice-scale_logo.png">
</p>

<h3 align="center">
    Команда БЕЗУМЦЫ
</h3>
<p align="center">
    Хакатон Practice&amp;Scale AI от Акселератора Возможностей
</p> 

Помимо реализации алгоритма обработки звонка, была разработана полноценная система оценки качества звонков и помощи операторам при взаимодействии с клиентами. Сайт доступен по [psai.tw1.su](https://psai.tw1.su/)

---
### Инструкция 

1. `git clone https://github.com/ItsHimReally/Practice-Scale-AI`
2. Перейти в папку script_files и установить зависимости `cd script_files && pip install requirements.txt`
3. Скачать веса модели rubert-2 ([ссылка](https://drive.google.com/file/d/1WEIKdmmZLRExeU8Mr2JjP-6HS6UBpY_x/view)), поместить в одну с директорию файлами script.py и models.py
4. Запуск скрипта:
   Предсказание: 
       `python script.py --dataset ./path_to_dataset --classifier_model ./path_to_model`
       ./path_to_model - путь к модели скачаной в 3 пункте, по умолчанию считается, что модель находится в директории script_files
   
   Обучение:
       Перед запуском в директории script_files нужно создать папку models для сохранения чекпоинтов моделей.
       `python script.py --learn --dataset ./path_to_dataset`
       Также для обучения необходим файл targets.csv, который нужно поместить в папку script_files. В нем должны присутствовать колонока traget (бин. занчение 0,1 для классов, и колонка id для соотнесения фалов в директории датасета с разметкой)
