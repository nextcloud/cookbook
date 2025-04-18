OC.L10N.register(
    "cookbook",
    {
    "No image with the matching MIME type was found on the server." : "서버에서 일치하는 MIME 유형의 이미지를 찾을 수 없습니다.",
    "Recipe with ID %d was not found in database." : "%d ID 레시피를 데이터베이스에서 찾을 수 없습니다.",
    "Downloading of a file failed returned the following error message: %s" : "파일 다운로드에 실패하여 다음 오류 메시지가 반환되었습니다: %s",
    "No content encoding was detected in the content." : "콘텐츠에서 콘텐츠 인코딩이 감지되지 않았습니다.",
    "The given image for the recipe %s cannot be parsed. Aborting and skipping it." : "레시피에 제공된 이미지 %s를 파싱할 수 없습니다. 중단하고 건너뜁니다.",
    "No valid recipe was left after heuristics of recipe %s." : "%s레시피 휴리스틱 이후 유효한 레시피가 남아있지 않습니다.",
    "Heuristics failed for image extraction of recipe %s." : "레시피 %s의 이미지 추출에 휴리스틱이 실패했습니다.",
    "Could not guess image URL as no recipe URL was found." : "레시피 URL이 없으므로 이미지 URL을 추측할 수 없습니다.",
    "Could not guess image URL scheme from recipe URL %s" : "레시피 URL %s에서 이미지 URL 구성표를 추측할 수 없습니다.",
    "Could not parse recipe ingredients. It is no array." : "레시피 재료를 파싱할 수 없습니다. 배열이 아닙니다.",
    "Could not parse recipe instructions as they are no array." : "배열이 아니기 때문에 레시피 지침을 구문 분석할 수 없습니다.",
    "Cannot parse recipe: Unknown object found during flattening of instructions." : "레시피를 구문 분석할 수 없습니다. 명령어를 평면화 중 알 수 없는 개체가 발견되었습니다.",
    "Did not find any p or li entries in the raw string of the instructions." : "지침의 원시 문자열에서 p 또는 li 항목을 찾지 못했습니다.",
    "Could not parse the keywords for recipe {recipe}." : "{recipe} 레시피에 대한 키워드를 구문 분석할 수 없습니다.",
    "Could not parse the nutrition information successfully for recipe {name}." : "{name} 레시피에 대한 영양 정보를 성공적으로 구문 분석할 수 없습니다.",
    "Using heuristics to parse the \"recipeYield\" field representing the number of servings of recipe {name}." : "휴리스틱을 사용하여 레시피 {name}의 제공량을 나타내는 \"recipeYield\" 필드를 구문 분석합니다.",
    "_Only a single number was found in the \"recipeYield\" field. Using it as number of servings._::_There are %n numbers found in the \"recipeYield\" field. Using the highest number found as number of servings._" : ["\"recipeYield\" 필드에 %n 수가 있습니다. 찾은 가장 높은 숫자를 제공량으로 사용합니다."],
    "Could not parse \"recipeYield\" field. Falling back to 1 serving." : "\"recipeYield\" 필드를 구문 분석할 수 없습니다. 1인분으로 줄었습니다.",
    "Could not parse recipe tools. Expected array or string." : "레시피 도구를 구문 분석할 수 없습니다. 배열 또는 문자열이 필요합니다.",
    "Could not find recipe in HTML code." : "HTML 코드에서 레시피를 찾을 수 없습니다.",
    "JSON cannot be decoded." : "JSON을 디코딩할 수 없습니다.",
    "No recipe was found." : "레시피를 찾을 수 없습니다.",
    "Parsing of HTML failed." : "HTML 구문 분석 실패",
    "Unsupported error level during parsing of XML output." : "XML 출력 구문 분석 중 지원하지 않는 오류 발생",
    "_Warning %u occurred while parsing %s._::_Warning %u occurred %n times while parsing %s._" : ["%s 구문 분석 중 경고 %u이(가) %n회 발생함."],
    "_Error %u occurred while parsing %s._::_Error %u occurred %n times while parsing %s._" : ["%s 구문 분석 중 오류 %u이(가) %n회 발생함."],
    "_Fatal error %u occurred while parsing %s._::_Fatal error %u occurred %n times while parsing %s._" : ["%s 구문 분석 중 치명적 오류 %u이(가) %n회 발생함."],
    "First time it occurred in line %u and column %u" : "%u행 %u열에서 처음 발생했습니다.",
    "Could not parse duration {duration}" : "{duration} 기간을 구문 분석할 수 없습니다.",
    "The recipe has already an image file. Cannot create a new one." : "레시피에 이미 이미지 파일이 있습니다. 새 항목을 만들 수 없습니다.",
    "There is no primary image for the recipe present." : "현재 레시피에 대한 기본 이미지가 없습니다.",
    "Cannot parse non-POST multipart encoding. This is a bug." : "비 POST 멀티파트 인코딩을 구문 분석할 수 없습니다. 버그가 발생하였습니다.",
    "Unsupported type of transmitted data. This is a bug, please report it." : "지원되지 않는 전송된 데이터 유형입니다. 이것은 버그이므로 신고해주세요.",
    "Cannot detect type of transmitted data. This is a bug, please report it." : "전송된 데이터 유형을 감지할 수 없습니다. 버그가 발생했습니다. 버그를 신고해주세요.",
    "Invalid URL-encoded string found. Please report a bug." : "잘못된 URL 인코딩 문자열이 발견되었습니다. 버그를 신고해주세요.",
    "Could not parse timestamp {timestamp}" : "타임스탬프 {timestamp}을(를) 구문 분석할 수 없습니다.",
    "The user is not logged in. No user configuration can be obtained." : "사용자가 로그인되어 있지 않습니다. 사용자 구성을 얻을 수 없습니다.",
    "Recipes" : "조리법들",
    "The user folder cannot be created due to missing permissions." : "권한 누락으로 인해 사용자 폴더를 생성할 수 없습니다.",
    "The configured user folder is a file." : "구성된 사용자 폴더는 파일입니다.",
    "User cannot create recipe folder" : "사용자는 레시피 폴더를 생성할 수 없습니다.",
    "in %s" : "%s",
    "Cannot read content of JSON file %s" : "%s JSON 파일의 내용을 읽을 수 없습니다.",
    "The JSON file in the folder with ID %d does not have a valid name." : "ID %d인 폴더에 있는 JSON 파일의 이름이 유효하지 않습니다.",
    "Could not parse URL" : "URL을 구문 분석 할 수 없습니다.",
    "Exception while downloading recipe from %s." : "%s로부터 레시피를 다운로드하는 도중에 예외가 발생했습니다.",
    "Download from %s failed as HTTP status code %d is not in expected range." : "HTTP 상태 코드 %d가 예상 범위를 벗어났기 때문에 %s에서 다운로드하지 못했습니다.",
    "Could not find a valid encoding when parsing %s." : "%s 구문 분석 중 유효한 인코딩을 찾을 수 없음.",
    "No parser found for the given import." : "해당 가져오기에 대한 파서를 찾을 수 없습니다.",
    "No recipe name was given. A unique name is required to store the recipe." : "레시피 이름이 제공되지 않았습니다. 레시피를 저장하려면 고유한 이름이 필요합니다.",
    "Unexpected node received for recipe folder." : "레시피 폴더에 대하여 예상치 못한 노드를 수신되었습니다.",
    "Another recipe with that name already exists" : "같은 이름을 가진 다른 레시피가 이미 존재합니다.",
    "Cannot download image using curl" : "curl을 사용하여 이미지를 다운로드할 수 없습니다",
    "No recipe data found. This is a bug" : "레시피 데이터가 없습니다. 버그가 발생하였습니다.",
    "Recipe with ID %d not found." : "%d ID 레시피를 찾을 수 없습니다.",
    "Image size \"%s\" is not recognized." : "이미지 크기 \"%s\"가 인식되지 않습니다.",
    "The full-sized image is not a thumbnail." : "완전한 크기의 이미지는 썸네일이 아닙니다.",
    "The thumbnail type %d is not known." : "썸네일 타입 %d은(는) 알려지지 않았습니다.",
    "Cookbook" : "요리책",
    "An integrated cookbook using schema.org JSON files as recipes" : "Schema.org JSON 파일을 레시피로 사용하는 통합 요리책",
    "A library for all your recipes. It uses JSON files following the schema.org recipe format. To add a recipe to the collection, you can paste in the URL of the recipe, and the provided web page will be parsed and downloaded to whichever folder you specify in the app settings." : "모든 레시피를 위한 라이브러리입니다.Schema.org 레시피 형식을 따르는 JSON 파일을 사용합니다. 컬렉션에 레시피를 추가하기 위해 레시피의 URL을 붙여넣으면 제공된 웹페이지가 구문 분석되어 앱 설정에서 지정한 폴더에 다운로드됩니다.",
    "Category" : "분류",
    "Recipe name" : "레시피 이름",
    "Tags" : "태그",
    "Search for recipes" : "레시피 검색",
    "Are you sure you want to delete this recipe?" : "이 레시피를 정말로 삭제하시겠습니까?",
    "Delete failed" : "삭제 실패",
    "Editing recipe" : "레시피 편집",
    "Viewing recipe" : "레시피 보기",
    "All recipes" : "모든 레시피",
    "None" : "없음",
    "Loading app" : "앱 로딩 중...",
    "Loading recipe" : "레시피 로딩 중...",
    "Recipe not found" : "레시피를 찾을 수 없음",
    "Page not found" : "페이지를 찾을 수 없음",
    "Creating new recipe" : "새로운 레시피 생성",
    "Edit" : "편집",
    "Save" : "저장",
    "Search" : "검색",
    "Filter current recipes" : "현재 레시피 필터링",
    "Filter" : "필터",
    "Search recipes" : "레시피 검색",
    "Reload recipe" : "레시피 새로고침",
    "Abort editing" : "편짐 중단",
    "Print recipe" : "레시피 출력",
    "Clone recipe" : "레시피 복사",
    "Delete recipe" : "레시피 삭제",
    "Path to your recipe collection" : "레시피 컬렉션 경로 지정",
    "Cannot access recipe folder." : "레시피 폴더에 접근할 수 없습니다.",
    "Select recipe folder" : "레시피 폴더 선택",
    "You are logged in with a guest account. Therefore, you are not allowed to generate arbitrary files and folders on this Nextcloud instance. To be able to use the Cookbook app as a guest, you need to specify a folder where all recipes are stored. You will need write permission to this folder." : "현재 손님 계정으로 로그인한 상태입니다. 따라서, 이 Nextcloud 인스턴스에 임의의 파일이나 폴더를 생성할 수 없습니다. 손님 계정으로 Cookbook 앱을 사용하기 위해 레시피가 저장될 폴더를 지정해야 하며, 해당 폴더의 쓰기 권한을 보유하고 있어야 합니다.",
    "Failed to load category {category} recipes" : "{category} 카테고리의 레시피를 불러오는데 실패했습니다.",
    "Failed to update name of category \"{category}\"" : "\"{category}\" 카테고리 이름을 업데이트하지 못했습니다.",
    "The server reported an error. Please check." : "서버에서 오류를 보고했습니다. 확인해주세요.",
    "Could not query the server. This might be a network problem." : "서버에 쿼리할 수 없습니다. 네트워크 문제일 수 있습니다.",
    "Loading category recipes …" : "카테고리 레시피 불러오는 중...",
    "Failed to fetch categories" : "카테고리를 가져오지 못했습니다.",
    "Create recipe" : "레시피 생성",
    "Download recipe from URL" : "URL로 레시피 다운로드",
    "Uncategorized recipes" : "분류되지 않은 레시피",
    "Categories" : "분류",
    "Rename" : "이름 바꾸기",
    "Enter new category name" : "새로운 카테고리 이름 입력",
    "Cookbook settings" : "요리책 설정",
    "Path to your recipe image" : "레시피 이미지 경로 지정",
    "Enter URL or select from your Nextcloud instance on the right" : "URL을 입력하거나 오른쪽의 Nextcloud 인스턴스에서 선택하세요.",
    "Pick a local image" : "장치에서 이미지 선택",
    "Move entry up" : "항목을 위로 이동",
    "Move entry down" : "항목을 아래로 이동",
    "Insert entry above" : "위에 항목 삽입",
    "Delete entry" : "항목 삭제",
    "Add" : "추가",
    "Select option" : "옵션 선택",
    "Delete nutrition item" : "영양항목 삭제",
    "Please select option first." : "옵션을 먼저 선택해주세요.",
    "No recipes created or imported." : "생성되거나 가져와진 레시피가 없습니다.",
    "To get started, you may use the text box in the left navigation bar to import a new recipe. Click below to create a recipe from scratch." : "시작하려면 왼쪽 탐색 표시줄의 텍스트 상자를 사용하여 새 레시피를 가져올 수 있습니다. 처음부터 레시피를 만들려면 아래를 클릭하세요.",
    "No recipes" : "레시피 없음",
    "Create new recipe!" : "새로운 레시피 생성!",
    "Name" : "이름",
    "Show settings for filtering recipe list" : "레시피 목록 필터링 설정 표시",
    "Order" : "순서",
    "Show filter settings" : "필터 설정 표시",
    "Filter name" : "필터 이름",
    "Search term" : "검색어",
    "All categories" : "모든 카테고리",
    "Show recipes containing any selected category" : "선택한 카테고리가 포함된 레시피 표시",
    "Show recipes containing all selected categories" : "선택한 카테고리가 모두 포함된 레시피 표시",
    "All keywords" : "모든 키워드",
    "Keywords" : "키워드",
    "Show recipes containing any selected keyword" : "선택한 키워드가 포함된 레시피 표시",
    "Show recipes containing all selected keywords" : "선택한 키워드가 모두 포함된 레시피 표시",
    "Clear" : "비우기",
    "Recipe filters" : "레시피 필터",
    "Matching all selected categories" : "선택한 모든 카테고리와 일치",
    "Matching any selected category" : "선택한 카테고리와 일치",
    "Matching all selected keywords" : "선택한 모든 키워드와 일치",
    "Matching any selected keyword" : "선택한 키워드와 일치",
    "Apply" : "적용",
    "Order keywords by number of recipes" : "레시피 수에 따라 키워드 정렬",
    "Order keywords alphabetically" : "키워드를 알파벳순으로 정렬",
    "Toggle keyword" : "키워드 전환",
    "Keyword not contained in visible recipes" : "표시되는 레시피에 포함되지 않은 키워드",
    "Toggle keyword area size" : "키워드 영역 크기 전환",
    "Creation date" : "생성일",
    "Modification date" : "수정일",
    "Select order" : "순서 선택",
    "Could not set preference for image printing" : "이미지 인쇄에 대한 기본 설정을 지정할 수 없습니다.",
    "Could not set recipe update interval to {interval}" : "레시피 업데이트 간격을 {interval}(으)로 설정할 수 없습니다.",
    "Could not save visible info blocks" : "공개된 정보 블록을 저장할 수 없습니다.",
    "Could not set recipe folder to {path}" : "레시피 폴더를 {path}(으)로 설정할 수 없습니다.",
    "Recipe folder" : "레시피 폴더",
    "Rescan library" : "라이브러리 재탐색",
    "Please pick a folder" : "폴더를 선택해주세요",
    "Update interval in minutes" : "업데이트 간격(분)",
    "Recipe display settings" : "레시피 표시 설정",
    "Print image with recipe" : "레시피가 포함된 이미지 인쇄",
    "Show filters and sorting in recipe lists" : "레시피 목록에 필터 및 정렬 표시",
    "Info blocks" : "정보 블록",
    "Control which blocks of information are shown in the recipe view. If you do not use some features and find them distracting, you may hide them." : "레시피 보기에 표시되는 정보 블록을 제어하십시오. 일부 기능을 사용하지 않고 불필요하다고 생각하면 해당 기능을 숨길 수 있습니다.",
    "Preparation time" : "준비 시간",
    "Cooking time" : "조리 시간",
    "Total time" : "총 시간",
    "Nutrition information" : "영양소 정보",
    "Tools" : "도구",
    "Frontend debug settings" : "프론트엔드 디버그 설정",
    "This allows to temporarily enable logging in the browser console in case of problems. You will not need these settings by default." : "이를 통해 문제가 발생할 경우 브라우저 콘솔에 일시적으로 로그인을 활성화할 수 있습니다. 기본적으로 이러한 설정은 필요하지 않습니다.",
    "Enable debugging" : "디버깅 활성화",
    "Dismiss" : "무시",
    "Cancel" : "취소",
    "OK" : "확인",
    "The page was not found" : "페이지를 찾을 수 없습니다",
    "You have unsaved changes! Do you still want to leave?" : "저장되지 않은 변경사항이 있습니다. 정말 돌아가시겠습니까?",
    "Calories" : "칼로리",
    "E.g.: 450 kcal (amount & unit)" : "예: 450kcal(양 및 단위)",
    "Carbohydrate content" : "탄수화물 함량",
    "E.g.: 2 g (amount & unit)" : "예: 2g(양 및 단위)",
    "Cholesterol content" : "콜레스테롤 함량",
    "Fat content" : "지방 함량",
    "Fiber content" : "섬유질 함량",
    "Protein content" : "단백질 함량",
    "Saturated-fat content" : "포화지방 함량",
    "Serving size" : "제공량",
    "Enter serving size (volume or mass)" : "제공량(부피 또는 질량)을 입력하세요.",
    "Sodium content" : "나트륨 함량",
    "Sugar content" : "당 함량",
    "Trans-fat content" : "트랜스지방 함량",
    "Unsaturated-fat content" : "불포화지방 함량",
    "Failed to fetch keywords" : "키워드를 가져오지 못했습니다.",
    "Unknown answer returned from server. See logs." : "서버에서 알 수 없는 답변이 반환되었습니다. 로그를 참조하세요.",
    "No answer for request was received." : "요청에 대한 답변을 받지 못했습니다.",
    "Could not start request to save recipe." : "레시피 저장 요청을 시작할 수 없습니다.",
    "Clone of {name}" : "{name}의 클론",
    "Loading recipe failed" : "레시피를 로딩 실패",
    "Description" : "설명",
    "URL" : "URL",
    "Image" : "이미지",
    "Preparation time (hours:minutes:seconds)" : "준비 시간(시:분:초)",
    "Cooking time (hours:minutes:seconds)" : "조리 시간(시:분:초)",
    "Total time (hours:minutes:seconds)" : "총 시간(시:분:초)",
    "Choose category" : "카테고리 선택",
    "Choose keywords" : "키워드 선택",
    "Servings" : "인분",
    "Toggle if the number of servings is present" : "인분 수가 있으면 전환합니다.",
    "Nutrition Information" : "영양 정보",
    "Pick option" : "옵션 선택",
    "Ingredients" : "재료",
    "Instructions" : "지침",
    "Recipe image" : "레시피 이미지",
    "Cooking time is up!" : "요리 시간이 다 됐습니다!",
    "{item} copied to clipboard" : "{item}이 클립보드에 복사되었습니다.",
    "Copying {item} to clipboard failed" : "{item}을 클립보드에 복사하지 못했습니다.",
    "ingredients" : "재료",
    "Loading…" : "불러오는 중…",
    "Search recipes with this keyword" : "이 키워드로 레시피 검색",
    "Date created" : "생성 일자",
    "Last modified" : "수정한 날짜",
    "Source" : "원본",
    "Copy ingredients to the clipboard" : "클립보드에 재료 복사",
    "Copy ingredients" : "재료 복사",
    "Serving Size" : "제공량",
    "Energy" : "에너지",
    "Sugar" : "당",
    "Carbohydrate" : "탄수화물",
    "Cholesterol" : "콜레스테롤",
    "Fiber" : "섬유질",
    "Protein" : "단백질",
    "Sodium" : "나트륨",
    "Fat total" : "지방 총계",
    "Saturated Fat" : "포화 지방",
    "Unsaturated Fat" : "불포화 지방",
    "Trans Fat" : "트랜스지방",
    "The ingredient cannot be recalculated due to incorrect syntax. Please ensure the syntax follows this format: amount unit ingredient and that a specific number of portions is set for this function to work correctly. Examples: 200 g carrots or 1 pinch of salt." : "잘못된 구문으로 인해 재료를 다시 계산할 수 없습니다.구문이 다음 형식을 따르는지 확인하십시오: 양 단위 성분 및 특정 부분 수가 설정되어 있는지 확인하십시오. 예: 당근 200g 또는 소금 1꼬집.",
    "Failed to load recipes with keywords: {tags}" : "키워드가 포함된 레시피를 로드하지 못했습니다: {tags}",
    "Failed to load search results" : "검색결과를 불러오지 못했습니다."
},
"nplurals=1; plural=0;");
