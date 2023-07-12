OC.L10N.register(
    "cookbook",
    {
    "No image with the matching MIME type was found on the server." : "لم يتم العثور على أي صورة بنوع MIME المطابق على الخادوم.",
    "Recipe with ID %d was not found in database." : "لم يتم العثور على الوصفة رقم %dفي قاعدة البيانات.",
    "Downloading of a file failed returned the following error message: %s" : "فشل تنزيل ملف و أرجع رسالة الخطأ التالية : %s",
    "No content encoding was detected in the content." : "تعذّر التعرُّف على ماهية ترميز المحتوى في المحتوى",
    "The given image for the recipe %s cannot be parsed. Aborting and skipping it." : "لا يمكن تحليل الصورة المقدمة للوصفة%s. تجاهلها و تخطَّها.",
    "No valid recipe was left after heuristics of recipe %s." : "لم تتبقَّ وصفة صالحة بعد الاستدلال heuristics على الوصفة %s.",
    "Heuristics failed for image extraction of recipe %s." : "فشل الاستدلال heuristics في استخلاص extraction صورة الوصفة%s.",
    "Could not guess image URL as no recipe URL was found." : "تعذر تخمين عنوان URL للصورة حيث لم يتم العثور على عنوان URL للوصفة.",
    "Could not guess image URL scheme from recipe URL %s" : "تعذر تخمين scheme مُخطّط URL للصورة من عنوان URL للوصفة %s",
    "Could not parse recipe ingredients. It is no array." : "تعذر تحليل مقادير الوصفة. حيث هي ليست مصفوفة.",
    "Could not parse recipe instructions as they are no array." : "تعذر تحليل تعليمات الوصفة حيث لا توجد مصفوفة.",
    "Cannot parse recipe: Unknown object found during flattening of instructions." : "لا يمكن تحليل الوصفة: تم العثور على كائن غير معروف أثناء تسوية التعليمات.",
    "Did not find any p or li entries in the raw string of the instructions." : "لم يتم العثور على أي مداخل p أو li في السلسلة الأولية للتعليمات.",
    "Could not parse the keywords for recipe {recipe}." : "تعذّر تحليل الكلمات الدلالية للوصفة {recipe}.",
    "Could not parse the nutrition information successfully for recipe {name}." : "تعذّر تحليل معلومات التركيبة الغذائية للوصفة {name} بنجاح.",
    "Using heuristics to parse the \"recipeYield\" field representing the number of servings of recipe {name}." : "استخدام الاستدلال لتحليل حقل \"recipeYield\" الذي يمثل عدد حصص servings الوصفة {name}.",
    "_Only a single number was found in the \"recipeYield\" field. Using it as number of servings._::_There are %n numbers found in the \"recipeYield\" field. Using the highest number found as number of servings._" : ["تم العثور على %n أرقام في حقل \"recipeYield\". استخدام أكبر رقم تم العثور عليه كعدد حصص التقديم.","تم العثور عل رقم فردي \"recipeYield\". تم استخدامه كرقم لعدد حصص التقديم.","تم العثور على %n أرقام في حقل \"recipeYield\". استخدام أكبر رقم تم العثور عليه كعدد حصص التقديم.","تم العثور على م%n أرقام في حقل \"recipeYield\". استخدام أكبر رقم تم العثور عليه كعدد حصص التقديم.","تم العثور على %n أرقام في حقل \"recipeYield\". استخدام أكبر رقم تم العثور عليه كعدد حصص التقديم.","تم العثور على%nأرقام في حقل \"recipeYield\". استخدام أكبر رقم تم العثور عليه كعدد حصص التقديم."],
    "Could not parse \"recipeYield\" field. Falling back to 1 serving." : "تعذّر تحليل حقل \"recipeYield\". العودة لاعتبارها حصة واحدة 1.",
    "Could not parse recipe tools. It is no array." : "تعذّر تحليل أدوات tools الوصفة. حيث أنها لا تمثل مصفوفة.",
    "Could not find recipe in HTML code." : "تعذّر العثور على الوصفة في كود HTML.",
    "JSON cannot be decoded." : "لا يمكن فك ترميز JSON",
    "No recipe was found." : "لم يتم العثور على أيّ وصفة.",
    "Parsing of HTML failed." : "فشل تحليل HTML.",
    "Unsupported error level during parsing of XML output." : "مستوى خطأ غير مدعوم أثناء تحليل مخرجات XML.",
    "_Warning %u occurred while parsing %s._::_Warning %u occurred %n times while parsing %s._" : ["تولد تحذير%u %n مرات أثناء تحليل%s.","تولد تحذير%u  أثناء تحليل%s.","تولد تحذير%u %n مرات أثناء تحليل%s.","تولد تحذير %u %nمرات أثناء تحليل%s.","تولد تحذير %u %nمرات أثناء تحليل %s.","تولد تحذير %u %n مرات أثناء تحليل%s."],
    "_Error %u occurred while parsing %s._::_Error %u occurred %n times while parsing %s._" : ["حدث خطأ%u %n مرات أثناء تحليل%s.","حدث خطأ%u أثناء تحليل%s.","حدث خطأ%u %n مرات أثناء تحليل%s.","حدث خطأ%u %n مرات أثناء تحليل%s.","حدث خطأ %u %nمرات أثناء تحليل%s.","حدث خطأ %u %nمرات أثناء تحليل%s."],
    "_Fatal error %u occurred while parsing %s._::_Fatal error %u occurred %n times while parsing %s._" : ["حدث خطأ %uخطير %nمرات أثناء تحليل%s.","حدث خطأ %uخطير أثناء تحليل %s.","حدث خطأ %uخطير %nمرات أثناء تحليل%s .","حدث خطأ %uخطير %nمرات أثناء تحليل%s .","حدث خطأ %u فادحٌ %nمرات أثناء تحليل %s.","حدث خطأ %u خطير %nمرات أثناء تحليل%s."],
    "First time it occurred in line %u and column %u" : "أول حدوث في السطر %u و العمود %u",
    "Could not parse duration {duration}" : "تعذّر تحليل المدة duration ـ  {duration}",
    "The recipe has already an image file. Cannot create a new one." : "الوصفة لها ملف صورة بالفعل. لا يمكن إنشاء واحد جديد.",
    "There is no primary image for the recipe present." : "لا توجد صورة أولية للوصفة الحالية.",
    "Cannot parse non-POST multipart encoding. This is a bug." : "لا يمكن تحليل ترميز متعدد الأجزاء  غير الموافق لـ POST. هذا خطأ.",
    "Cannot detect type of transmitted data. This is a bug, please report it." : "لا يمكن الكشف عن نوع البيانات المرسلة. هذا خطأ، يرجى الإبلاغ عنه.",
    "Invalid URL-encoded string found. Please report a bug." : "تم العثور على سلسلة مرمزة بعنوان URL غير صالحة. الرجاء الإبلاغ عن خطأ.",
    "The user is not logged in. No user configuration can be obtained." : "لم يتم تسجيل دخول المستخدم. لا يمكن الحصول على تكوين المستخدم.",
    "Recipes" : "وصفات طبخ Recipes",
    "The user folder cannot be created due to missing permissions." : "لا يمكن إنشاء مجلد المستخدم بسبب نقص الأذونات.",
    "The configured user folder is a file." : "مجلد المستخدم الذي تمّ تكوينه هو ملف.",
    "User cannot create recipe folder" : "لا يمكن للمستخدم إنشاء مجلد لوصفة",
    "in %s" : "في %s",
    "The JSON file in the folder with ID %d does not have a valid name." : "ملف JSON في المجلد الذي مُعرّفُه %d اسمه غير صحيح.",
    "Could not parse URL" : "تعذّر تحليل عنوان URL",
    "Exception while downloading recipe from %s." : "استثناء أثناء تنزيل الوصفة من %s.",
    "Download from %s failed as HTTP status code %d is not in expected range." : "التنزيل من %s فشل بسب أن كود حالة HTTP ـ %d ليس ضمن المدى المتوقع.",
    "Could not find a valid encoding when parsing %s." : "تعذّر إيجاد ترميز صحيح عند تحليل %s.",
    "No parser found for the given import." : "تعذّر إيجاد مُحلّل parser في المُستَوْرَد",
    "No recipe name was given. A unique name is required to store the recipe." : "لم يتم إعطاء اسم وصفة. مطلوب اسم فريد لتخزين الوصفة.",
    "Another recipe with that name already exists" : "وصفة أخرى بهذا الاسم موجودة سلفاً",
    "No recipe data found. This is a bug" : "لم يتم العثور على بيانات الوصفة. هناك خطأ",
    "Recipe with ID %d not found." : "الوصفة ذات الرقم %dغير موجودة.",
    "Image size \"%s\" is not recognized." : "الصورة \"%s\" لم يمكن التعرُّف على حجمها.",
    "The full-sized image is not a thumbnail." : "الصورة في حجمها الأصلي و ليست مُصغَّرة",
    "The thumbnail type %d is not known." : "نوع تصغير الصورة %d غير معروف.",
    "Cookbook" : "كتاب الطهو Cookbook",
    "An integrated cookbook using schema.org JSON files as recipes" : "كتاب طهوٍ متكامل للوصفات من schema.org يستخدم الملفات ذات التنسيق JSON كوصفات.",
    "A library for all your recipes. It uses JSON files following the schema.org recipe format. To add a recipe to the collection, you can paste in the URL of the recipe, and the provided web page will be parsed and downloaded to whichever folder you specify in the app settings." : "مكتبة لجميع وصفاتك. والتي تستخدم تنسيق JSON استناداً إلى تنسيق الوصفات من schema.org. لإضافة وصفة إلى المجموعة، يمكنك لصق عنوان URL الخاص بالوصفة، و سيتم تحليل صفحة الويب المقدمة وتنزيلها إلى المجلد الذي تحدده في إعدادات التطبيق.",
    "Editing recipe" : "تحرير الوصفة ",
    "Viewing recipe" : "مشاهدة الوصفة ",
    "All recipes" : "كل الوصفات",
    "None" : "غير محدد",
    "Loading app" : "تحميل التطبيق ",
    "Loading recipe" : "تحميل الوصفة",
    "Recipe not found" : "الوصفة غير موجودة",
    "Page not found" : "الصفحة غير موجودة",
    "Creating new recipe" : "إنشاء وصفة جديدة",
    "Edit" : "تعديل",
    "Save" : "حِفظ",
    "Search" : "بحث",
    "Reload recipe" : "اعادة تحميل الوصفة",
    "Abort editing" : "خروج من التحرير",
    "Print recipe" : "طباعة الوصفة",
    "Delete recipe" : "حذف الوصفة",
    "Filter" : "فلتر",
    "Category" : "التصنيف",
    "Recipe name" : "اسم الوصفة",
    "Tags" : "وُسُوم",
    "Search for recipes" : "البحث عن وصفاتٍ",
    "Are you sure you want to delete this recipe?" : "هل أنت متأكد أنك تريد حذف هذه الوصفة؟",
    "Delete failed" : "فشل الحذف",
    "Cannot access recipe folder." : "لا يمكن الوصول إلى مجلد الوصفة.",
    "You are logged in with a guest account. Therefore, you are not allowed to generate arbitrary files and folders on this Nextcloud instance. To be able to use the Cookbook app as a guest, you need to specify a folder where all recipes are stored. You will need write permission to this folder." : "لقد قمت بتسجيل الدخول بحساب ضيف. لذلك، لا يُسمح لك بإنشاء أي ملفات ومجلدات على خادوم نكست كلاود هذا. لتتمكن من استخدام تطبيق \"كتاب الطهو\" Cookbook كضيفٍ، تحتاج إلى تحديد مجلد سيتم تخزين الوصفات، و ستحتاج إلى إذن بالكتابة في هذا المجلد.",
    "Select recipe folder" : "حدّد مُجلّد الوصفة",
    "Path to your recipe collection" : "المسار إلى مجموعة الوصفات الخاصة بك",
    "Create recipe" : "إنشاء وصفة",
    "Uncategorized recipes" : "وصفات غير مُصنّفة",
    "Categories" : "التصنيفات",
    "Rename" : "تغيير تسمية",
    "Enter new category name" : "أدخِل اسم التصنيف الجديد",
    "Cookbook settings" : "إعدادات كتاب الطهو",
    "Download recipe from URL" : "قم بتنزيل الوصفة من عنوان URL",
    "Failed to load category {category} recipes" : "فشل تحميل تصنيف الوصفة {category}",
    "The server reported an error. Please check." : "أبلغ الخادوم عن خطأ. يرجى المراجعة.",
    "Could not query the server. This might be a network problem." : "لا يمكن الاستعلام عن الخادوم. قد تكون هذه مشكلة في الشبكة.",
    "Loading category recipes …" : "جارٍ تحميل تصنيفات الوصفات ...",
    "Failed to fetch categories" : "فشل في جلب التصنيفات",
    "Enter URL or select from your Nextcloud instance on the right" : "أدخِل عنوان URL أو إختَر من خادوم نكست كلاود في الجانب",
    "Pick a local image" : "إختَر صورةً محليّةً",
    "Path to your recipe image" : "المسار إلى صورة الوصفة الخاصة بك",
    "Move entry up" : "نقل المَدْخَل للأعلى",
    "Move entry down" : "نقل المَدْخَل للأسفل",
    "Insert entry above" : "إدخال المَدْخَل أعلاه",
    "Delete entry" : "إحذِف المَدْخَل",
    "Add" : "إضافة",
    "Close" : "إغلاق",
    "The page was not found" : "لم يتم العثور على الصفحة",
    "Name" : "الاسم",
    "Description" : "الوصف",
    "URL" : "الرابط",
    "Image" : "صورة",
    "Preparation time (hours:minutes)" : "زمن التحضير (hours:minutes)",
    "Cooking time (hours:minutes)" : "زمن الطهو (hours:minutes)",
    "Total time (hours:minutes)" : "الزمن الكُلِّي (hours:minutes)",
    "Choose category" : "اختر التصنيف",
    "Keywords" : "الكلمات المفتاحية",
    "Choose keywords" : "إختر الكلمات الدّلالية",
    "Servings" : "عدد الأشخاص Servings",
    "Toggle if the number of servings is present" : "بدّل إذا كان عدد الأشخاص servings موجوداً",
    "Nutrition Information" : "معلومات التغذية",
    "Pick option" : "إلتقِط خياراً",
    "Tools" : "الأدوات",
    "Ingredients" : "المُكَوِّنات",
    "Instructions" : "التعليمات",
    "You have unsaved changes! Do you still want to leave?" : "لديك تغييرات غير محفوظة! هل مازلت تريد الخروج؟",
    "Calories" : "السُّعرات الحرارية",
    "E.g.: 450 kcal (amount & unit)" : "على سبيل المثال: 450 سُعرة حرارية (الكَمّية و الوِحدة)",
    "Carbohydrate content" : "مُحتوى الكربوهيدرات",
    "E.g.: 2 g (amount & unit)" : "على سبيل المثال: 2 جم (الكَمّية و الوِحدة)",
    "Cholesterol content" : "مُحتوى الكوليسترول",
    "Fat content" : "مُحتوى الدهون",
    "Fiber content" : "مُحتوى الألياف",
    "Protein content" : "مُحتوى البروتين",
    "Saturated-fat content" : "مُحتوى الدهون المشبعة",
    "Serving size" : "حجم حصة التقديم لكل شخص",
    "Enter serving size (volume or mass)" : "أدخِل حجم حصة التقديم لكل شخص (الحجم أو الكتلة)",
    "Sodium content" : "مُحتوى الصوديوم",
    "Sugar content" : "مُحتوى السكر",
    "Trans-fat content" : "مُحتوى الدُّهُون المُتحوِّلة",
    "Unsaturated-fat content" : "مُحتوى الدُّهُون غير المُشبَّعة",
    "Failed to fetch keywords" : "فشل استدعاء الكلمات الدلالية",
    "Loading recipe failed" : "فشل تحميل الوصفة",
    "Unknown answer returned from server. See logs." : "أرجَعَ الخادوم جواباً غير معروفٍ. أنظر سجلات الحركات logs.",
    "No answer for request was received." : "لم يتم استلام ردّ علي الطلب.",
    "Could not start request to save recipe." : "تعذر بدء طلب حفظ الوصفة.",
    "Recipe image" : "صورة الوصفة",
    "Select order" : "إختَر ترتيباً",
    "Creation date" : "تاريخ الإنشاء",
    "Modification date" : "تاريخ التعديل",
    "Toggle keyword" : "تبديل الكلمات الدلالية ",
    "Keyword not contained in visible recipes" : "الكلمة الدلالية غير واردة في الوصفات المرئية",
    "Toggle keyword area size" : "تبديل حجم مساحة الكلمات الدلالية ",
    "Order keywords by number of recipes" : "ترتيب الكلمات الدلالية حسب عدد الوصفات",
    "Order keywords alphabetically" : "ترتيب الكلمات الدلالية أبجدياً",
    "Cooking time is up!" : "إنتهى وقت الطهو!",
    "Search recipes with this keyword" : "البحث عن وصفات فيها هذه الكلمة",
    "Date created" : "تم إنشاء التاريخ",
    "Last modified" : "آخر تعديل",
    "Preparation time (H:MM)" : "وقت التحضير (H:MM)",
    "Cooking time (H:MM)" : "وقت الطهو (H:MM)",
    "Total time (H:MM)" : "الوقت الكُلِّي (H:MM)",
    "Serving Size" : "حجم حصة التقديم للشخص الواحد",
    "Energy" : "الطاقة",
    "Sugar" : "السكر",
    "Carbohydrate" : "الكربوهيدرات",
    "Cholesterol" : "الكوليسترول",
    "Fiber" : "الألياف",
    "Protein" : "البروتين",
    "Sodium" : "الصوديوم",
    "Fat total" : "إجمالي الدهون",
    "Saturated Fat" : "دهون مُشبَّعة",
    "Unsaturated Fat" : "دهون غير مُشبّعة",
    "Trans Fat" : "دهون مُتحوِّلة",
    "Source" : "المصدر",
    "Copy ingredients" : "نسخ المكونات و المقادير",
    "The ingredient cannot be recalculated due to incorrect syntax. Please change it to this syntax: amount unit ingredient. Examples: 200 g carrots or 1 pinch of salt" : "لا يمكن إعادة حساب المكون بسبب تركيب غير صحيح. يرجى تغييره بحسب التسلسل التالي: كمية ثم وحدة المكون. أمثلة: 200 جرام جزر أو 1 رشة من الملح",
    "Loading…" : "التحميل جارٍ…",
    "Failed to load recipes with keywords: {tags}" : "فشل تحميل الوصفات بالكلمات الدلالية: {tags}",
    "Failed to load search results" : "فشل تحميل نتائج البحث",
    "Recipe folder" : "مجلد الوصفة",
    "Please pick a folder" : "الرجاء اختيار مجلد",
    "Recipe display settings" : "ضبط عرض الوصفة",
    "Info blocks" : "صناديق المعلومات ",
    "Rescan library" : "إعادة فحص المكتبة ",
    "Update interval in minutes" : "الفاصل الزمني للتحديث بالدقائق",
    "Print image with recipe" : "طباعة الصورة مع الوصفة",
    "Show keyword cloud in recipe lists" : "إظهار سحابة الكلمات الدلالية في قوائم الوصفات",
    "Control which blocks of information are shown in the recipe view. If you do not use some features and find them distracting, you may hide them." : "التحكم في كتل المعلومات التي تظهر في عرض الوصفة. إذا لم تكن تستخدم بعض الميزات و وجدتها مسببة للارتباك، فيمكنك إخفاؤها.",
    "Preparation time" : "وقت التحضير",
    "Cooking time" : "وقت الطهو",
    "Total time" : "الوقت الكُلِّي",
    "Nutrition information" : "معلومات التغذية",
    "Could not set preference for image printing" : "تعذر تعيين التفضيل في طباعة الصور",
    "Could not set recipe update interval to {interval}" : "تعذر تعيين الفاصل الزمني لتحديث الوصفة بـ {interval}",
    "Could not save visible info blocks" : "تعذّر حفظ صناديق المعلونات المرئية visible info blocks",
    "Could not set recipe folder to {path}" : "تعذر تعيين مجلد الوصفة في {path}",
    "Loading config failed" : "فشل تحميل التكوين",
    "Dismiss" : "تجاهل",
    "Cancel" : "إلغاء",
    "OK" : "موافق"
},
"nplurals=6; plural=n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n%100>=3 && n%100<=10 ? 3 : n%100>=11 && n%100<=99 ? 4 : 5;");
