OC.L10N.register(
    "cookbook",
    {
    "No image with the matching MIME type was found on the server." : "На сървъра не беше намерено изображение със съответстващ MIME тип.",
    "Recipe with ID %d was not found in database." : "Няма намерена рецепта с  ID %d в базата данни.",
    "Downloading of a file failed returned the following error message: %s" : "Неуспешното изтегляне на файл, върна следното съобщение за грешка: %s",
    "No content encoding was detected in the content." : "В съдържанието не е открито кодиране на съдържание.",
    "The given image for the recipe %s cannot be parsed. Aborting and skipping it." : "Даденото изображение за рецептата %s не може да бъде анализирано. Да се прекъсне и пропусне.",
    "No valid recipe was left after heuristics of recipe %s." : "Не е останала валидна рецепта след евристика на рецепта %s.",
    "Heuristics failed for image extraction of recipe %s." : "Неуспешна евристика за извличане на изображение на рецепта %s.",
    "Could not guess image URL as no recipe URL was found." : "Неуспешно предположение за URL адрес на изображение, тъй като не беше намерен URL адрес на рецепта.",
    "Could not guess image URL scheme from recipe URL %s" : " Неуспешно предположение за URL адрес на изображение на схемата   от URL адреса на рецептата %s",
    "Could not parse recipe ingredients. It is no array." : "Съставките на рецептата не можаха да бъдат анализирани. Не е масив.",
    "Could not parse recipe instructions as they are no array." : "Инструкциите за рецепта не можаха да бъдат анализирани, тъй като не са масив.",
    "Cannot parse recipe: Unknown object found during flattening of instructions." : "Рецептата не може да се анализира: Открит е неизвестен обект по време на изравняване на инструкции.",
    "Did not find any p or li entries in the raw string of the instructions." : "Не са намерени никакви p или li записи в необработения низ на инструкциите.",
    "Could not parse the keywords for recipe {recipe}." : "Ключовите думи за рецепта {recipe} не можаха да бъдат анализирани.",
    "Could not parse the nutrition information successfully for recipe {name}." : "Не можа да се анализира успешно хранителната информация за рецепта {name}.",
    "Using heuristics to parse the \"recipeYield\" field representing the number of servings of recipe {name}." : "Използване на евристика за анализиране на полето „рецептаПорции“, представляващо броя на порциите от рецептата {name}.",
    "_Only a single number was found in the \"recipeYield\" field. Using it as number of servings._::_There are %n numbers found in the \"recipeYield\" field. Using the highest number found as number of servings._" : ["Има намерени %n числа в полето „рецептаПорции“. Използване на най-голямото намерено число като брой порции.","Има намерени %n числа в полето „рецептаПорции“. Използване на най-голямото намерено число като брой порции."],
    "Could not parse \"recipeYield\" field. Falling back to 1 serving." : "Не можа да се анализира полето „рецептаПорции“. Връщане към 1 порция.",
    "Could not parse recipe tools. It is no array." : "Инструментите за рецептата не можаха да бъдат анализирани. Не е масив.",
    "Could not find recipe in HTML code." : "Не можа да се намери рецепта в HTML кода.",
    "JSON cannot be decoded." : "JSON файлът не може да се декодира.",
    "No recipe was found." : "Не беше намерена рецепта.",
    "Parsing of HTML failed." : "Анализът на HTML не беше успешен.",
    "Unsupported error level during parsing of XML output." : "Неподдържано ниво на грешка при анализиране на XML изход.",
    "_Warning %u occurred while parsing %s._::_Warning %u occurred %n times while parsing %s._" : ["Предупреждението %u се появи %n пъти при анализиране на %s.","Предупреждението %u се появи %n пъти при анализиране на %s."],
    "_Error %u occurred while parsing %s._::_Error %u occurred %n times while parsing %s._" : ["Грешка %u се появи %n пъти при анализиране на %s.","Грешка %u се появи %n пъти при анализиране на %s."],
    "_Fatal error %u occurred while parsing %s._::_Fatal error %u occurred %n times while parsing %s._" : ["Фатална грешка %u се появи %n пъти при анализиране на %s.","Фатална грешка %u се появи %n пъти при анализиране на %s."],
    "First time it occurred in line %u and column %u" : "За първи път се появи в ред %u и колона %u",
    "Could not parse duration {duration}" : "Продължителността {duration} не можа да се анализира",
    "The recipe has already an image file. Cannot create a new one." : "Рецептата вече има файл с изображение. Не може да се създаде нов.",
    "There is no primary image for the recipe present." : "Няма основно изображение за настоящата рецепта.",
    "Cannot parse non-POST multipart encoding. This is a bug." : "Не може да се анализира  multipart кодиране, различно от POST. Това е грешка.",
    "Cannot detect type of transmitted data. This is a bug, please report it." : "Не може да се открие типът на предавани данни. Това е грешка, моля, докладвайте.",
    "Invalid URL-encoded string found. Please report a bug." : "Намерен е невалиден низ, с кодиран URL адрес. Моля, докладвайте грешка.",
    "The user is not logged in. No user configuration can be obtained." : "Потребителят не се е вписал. Не може да бъде получена потребителска конфигурация.",
    "Recipes" : "Рецепти",
    "The user folder cannot be created due to missing permissions." : "Потребителската папка не може да бъде създадена поради липсващи права.",
    "The configured user folder is a file." : "Конфигурираната потребителска папка е файл.",
    "User cannot create recipe folder" : "Потребител не може да създаде папка с рецепти",
    "in %s" : "в %s",
    "The JSON file in the folder with ID %d does not have a valid name." : "JSON файлът в папка с Идентификатор %dняма валидно име.",
    "Could not parse URL" : "URL адресът не можа да бъде анализиран",
    "Exception while downloading recipe from %s." : "Изключение при изтегляне на рецепта от %s.",
    "Download from %s failed as HTTP status code %d is not in expected range." : "Изтеглянето от %s е неуспешно, тъй като HTTP кодът на състоянието %d не е в очаквания диапазон.",
    "Could not find a valid encoding when parsing %s." : "Не можа да се намери валидно кодиране при анализ на %s. ",
    "No parser found for the given import." : "Не е намерен анализатор за даденото импортиране.",
    "No recipe name was given. A unique name is required to store the recipe." : "Не е дадено име на рецептата. За съхраняване на рецептата е нужно уникално име.",
    "Another recipe with that name already exists" : "Друга рецепта с това име вече съществува",
    "No recipe data found. This is a bug" : "Няма намерени данни за рецепта. Това е грешка",
    "Recipe with ID %d not found." : "Няма намерена рецепта с  ID %d.",
    "Image size \"%s\" is not recognized." : "Размерът на изображение \"%s\" не е разпознат.",
    "The full-sized image is not a thumbnail." : "Изображение в пълен размер не е миниатюра.",
    "The thumbnail type %d is not known." : "Типът миниатюра %d не е известен.",
    "Cookbook" : "Готварска книга",
    "An integrated cookbook using schema.org JSON files as recipes" : "Интегрирана готварска книга, използваща JSON файлове schema.org като рецепти",
    "A library for all your recipes. It uses JSON files following the schema.org recipe format. To add a recipe to the collection, you can paste in the URL of the recipe, and the provided web page will be parsed and downloaded to whichever folder you specify in the app settings." : "Библиотека за всички ваши рецепти. Тя използва JSON файлове, следващи формата на рецептата schema.org. За да добавите рецепта към колекцията, можете да поставите URL адреса на рецептата и предоставената уеб страница ще бъде анализирана и изтеглена в папка, която посочите в настройките на приложението.",
    "Editing recipe" : "Редактиране на рецепта",
    "Viewing recipe" : "Преглед на рецепта",
    "All recipes" : "Всички рецепти",
    "Loading app" : "Приложението се зарежда",
    "Loading recipe" : "Зареждане на рецепта",
    "Recipe not found" : "Рецептата не е открита",
    "Page not found" : "Страницата не е намерена",
    "Creating new recipe" : "Създаване на нова рецепта",
    "Edit" : "Редактиране",
    "Save" : "Запиши",
    "Search" : "Търсене",
    "Reload recipe" : "Презареждане на рецепта",
    "Print recipe" : "Печат на рецепта",
    "Delete recipe" : "Изтриване на рецепта",
    "Filter" : "Филтър",
    "Category" : "Категория",
    "Recipe name" : "Име на рецепта",
    "Tags" : "Етикети",
    "Search for recipes" : "Търсене на рецепти",
    "Are you sure you want to delete this recipe?" : "Сигурни ли сте, че искате да изтриете тази рецепта?",
    "Delete failed" : "Неуспешно изтриване",
    "Cannot access recipe folder." : "Няма достъп до папка с рецепти.",
    "You are logged in with a guest account. Therefore, you are not allowed to generate arbitrary files and folders on this Nextcloud instance. To be able to use the Cookbook app as a guest, you need to specify a folder where all recipes are stored. You will need write permission to this folder." : "Влезли сте с профил на гост. Следователно не ви е позволено да генерирате произволни файлове и папки в този екземпляр на Nextcloud. За да можете да използвате приложението Cookbook като гост, трябва да посочите папка, където се съхраняват всички рецепти. Ще ви трябва право за запис в тази папка.",
    "Select recipe folder" : "Избор на папка с рецепти",
    "Path to your recipe collection" : "Път към вашата колекция от рецепти",
    "Create recipe" : "Създаване на рецепта",
    "Uncategorized recipes" : "Некатегоризирани рецепти",
    "Categories" : "Категории",
    "Rename" : "Преименуване",
    "Enter new category name" : "Въвеждане на ново име на категория",
    "Download recipe from URL" : "Изтегляне на рецепта от URL адрес",
    "Toggle editing" : "Превключване на редактирането ",
    "Failed to load category {category} recipes" : "Неуспешно зареждане на рецепти от категория {category}",
    "The server reported an error. Please check." : "Сървърът съобщи за грешка. Моля, проверете.",
    "Could not query the server. This might be a network problem." : "Не можа да се направи заявка към сървъра. Това може да е проблем с мрежата.",
    "Loading category recipes …" : "Зареждат се категории рецепти ...",
    "Failed to fetch categories" : "Неуспешно извличане на категории",
    "Rescan library" : "Повторно сканиране на библиотеката",
    "Please pick a folder" : "Моля, изберете папка",
    "Recipe folder" : "Папка с рецепти",
    "Update interval in minutes" : "Актуализиране на интервал в минути ",
    "Print image with recipe" : "Печат на изображение с рецепта",
    "Show keyword cloud in recipe lists" : "Показване на облак от ключови думи в списъци с рецепти",
    "Could not set preference for image printing" : "Неуспешно задаване на предпочитание за отпечатване на изображение",
    "Could not set recipe update interval to {interval}" : "Интервалът за актуализиране на рецептата не можа да бъде зададен на {interval}",
    "Could not set recipe folder to {path}" : "Папка с рецепти не можа да се зададе на {path}",
    "Loading config failed" : "Зареждането на конфигурацията е неуспешно",
    "Enter URL or select from your Nextcloud instance on the right" : "Въведете URL адрес или изберете от вашия екземпляр на Nextcloud вдясно",
    "Pick a local image" : "Избор на локално изображение",
    "Path to your recipe image" : "Път към изображението на вашата рецепта",
    "Move entry up" : "Преместване на записа нагоре",
    "Move entry down" : "Преместване на записа надолу",
    "Insert entry above" : "Вмъкване на запис над",
    "Delete entry" : "Изтриване на запис",
    "Add" : "Добави",
    "Close" : "Затваряне",
    "The page was not found" : "Страницата не беше намерена",
    "Name" : "Име",
    "Description" : "Описание",
    "URL" : "URL",
    "Image" : "Изображение",
    "Preparation time (hours:minutes)" : "Време за подготовка (часове:минути)",
    "Cooking time (hours:minutes)" : "Време за готвене (часове:минути)",
    "Total time (hours:minutes)" : "Общо време (часове:минути)",
    "Choose category" : "Избор на категория",
    "Keywords" : "Ключови думи",
    "Choose keywords" : "Избор на ключови думи",
    "Servings" : "Порции",
    "Toggle if the number of servings is present" : "Превключване, ако присъства броя на порциите",
    "Nutrition Information" : "Информация за храната",
    "Pick option" : "Избор на опция",
    "Tools" : "Инструменти",
    "Ingredients" : "Съставки",
    "Instructions" : "Инструкции",
    "You have unsaved changes! Do you still want to leave?" : "Имате незапазени промени! Все още ли искате да напуснете?",
    "Calories" : "Калории",
    "E.g.: 450 kcal (amount & unit)" : "Например: 450 kcal (количество и единици)",
    "Carbohydrate content" : "Съдържание на въглехидрати",
    "E.g.: 2 g (amount & unit)" : "Напр.: 2 g (количество и единици)",
    "Cholesterol content" : "Съдържание на холестерол",
    "Fat content" : "Съдържание на мазнини ",
    "Fiber content" : "Съдържание на фибри",
    "Protein content" : "Съдържание на протеин",
    "Saturated-fat content" : "Съдържание на наситени мазнини",
    "Serving size" : "Размер на порция",
    "Enter serving size (volume or mass)" : "Въвеждане на размер на порция (обем или маса)",
    "Sodium content" : "Съдържание на натрий",
    "Sugar content" : "Съдържание на захар",
    "Trans-fat content" : "Съдържание на трансмазнини",
    "Unsaturated-fat content" : "Съдържание на ненаситени мазнини",
    "Failed to fetch keywords" : "Неуспешно извличане на ключови думи ",
    "Loading recipe failed" : "Зареждането на рецептата е неуспешно",
    "Unknown answer returned from server. See logs." : "От сървъра е върнат неизвестен отговор. Вижте журналите. ",
    "No answer for request was received." : "Не е получен отговор на искането.",
    "Could not start request to save recipe." : "Не можа да стартира заявката за записване на рецепта.",
    "Recipe image" : "Изображение на рецепта",
    "Select order" : "Избор на подредба",
    "Creation date" : "Дата на създаване",
    "Modification date" : "Дата на модификация",
    "Toggle keyword" : "Превключване на ключова дума",
    "Keyword not contained in visible recipes" : "Ключовата дума не се съдържа във видимите рецепти",
    "Toggle keyword area size" : "Превключване на размер на областта на ключовите думи",
    "Order keywords by number of recipes" : "Подреждане на ключови думи по брой рецепти",
    "Order keywords alphabetically" : "Подреждане на ключови думи по азбучен ред",
    "Cooking time is up!" : "Времето за готвене изтече!",
    "Search recipes with this keyword" : "Търсене на рецепти с тази ключова дума",
    "Date created" : "Дата на създаване",
    "Last modified" : "Последна промяна",
    "Preparation time (H:MM)" : "Време за подготовка (Ч:ММ)",
    "Cooking time (H:MM)" : "Време за готвене (Ч:ММ)",
    "Total time (H:MM)" : "Общо време (Ч:ММ)",
    "Serving Size" : "Размер на порция",
    "Energy" : "Енергия",
    "Sugar" : "Захар",
    "Carbohydrate" : "Въглехидрати",
    "Cholesterol" : "Холестерол",
    "Fiber" : "Фибри",
    "Protein" : "Протеин",
    "Sodium" : "Натрий",
    "Fat total" : "Общо мазнини",
    "Saturated Fat" : "Наситени мазнини",
    "Unsaturated Fat" : "Ненаситени мазнини",
    "Trans Fat" : "Транс мазнини",
    "Source" : "Източник",
    "Loading…" : "Зареждане…",
    "Failed to load recipes with keywords: {tags}" : "Неуспешно зареждане на рецепти с ключови думи: {tags}",
    "Failed to load search results" : "Зареждането на резултатите от търсенето е неуспешно ",
    "Dismiss" : "Отхвърляне",
    "Cancel" : "Отказ",
    "OK" : "Добре",
    "None" : "Няма",
    "Abort editing" : "Прекратяване на редактирането",
    "Cookbook settings" : "Настройки на приложението Cookbook /готварска книга/",
    "Recipe display settings" : "Настройки за показване на рецепта",
    "Preparation time" : "Време за подготовка",
    "Cooking time" : "Време за готвене",
    "Total time" : "Общо време"
},
"nplurals=2; plural=(n != 1);");
