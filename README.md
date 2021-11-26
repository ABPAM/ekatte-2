## ЕКАТТЕ 2.0

*Забележка: Това е версия 2.0 на пакета „ЕКАТТЕ“. За версия 1.0 - [кликнете тук](https://github.com/ABPAM/ekatte)*

#### Какво е ЕКАТТЕ (като РНР пакет)?
Списък на населените места в Р. България, изтеглени от [НСИ (Национален Статистически Институт)](https://nsi.bg), сортирани по области и общини, във вид, удобен за използване в PHP среда. 

*ЕКАТТЕ - **Е**динен **К**ласификатор на **А**дминистративно-**Т**ериториалните и **Т**ериториалните **Е**диници.

#### Кой би имал нужда от подобно нещо?
Разработчици, работещи върху системи за:
-   Електронна търговия
-   Застрахователни услуги
-   Всичко останало, при разработката на което се налага работа с населени места (от потребители и/или администратори).

#### Какво е необходимо, за да използвам EKATTE?
-   php >= 5.6.0

#### Как да инсталирам EKATTE?
В composer.json файла на своя проект добавете:
```
"require": {
...
	"abpam/ekatte-2": "^0.0.1"
...
},

...

"scripts": {
...
	"setup-ekatte": [
		"@putenv EKATTE_URL=\"http://www.nsi.bg/sites/default/files/files/EKATTE/Ekatte.zip\"",
		"ABPAM\\Ekatte\\Ekatte::setup"
	],
...
}
```
Запишете composer.json и изпълнете `composer install && composer run setup-ekatte`


#### Как да използвам ЕКАТТЕ? (вместо документация :) )
|Функция|Описание|Връщана стойност|
|--|--|--|
|`Oblast::getList()`|Получаване на списък с всички области.| Array |
|`Oblast::getByName(string)`|Получаване на информация за дадена област, търсена по име. Пример: `Oblast::getByName('Велико Търново')` - Информация за област Велико Търново.| Array |
|`Oblast::getByCode(string)`|Получаване на информация за дадена област, търсена по трибуквен код (вж. „Пояснение за КОД“). Пример: `Oblast::getByCode('SHU')` - Информация за област Шумен.| Array |
|`Obshtina::getList()`|Получаване на списък с всички общини.| Array |
|`Obshtina::getByName(string)`|Получаване на информация за дадена община, търсена по име. Пример: `Obshtina::getByName('Чирпан')`.| Array |
|`Obshtina::getByCode(string)`|Получаване на информация за дадена община, търсена по идентификационен код (вж. „Пояснение за КОД“). Пример: `Obshtina::getByCode('VAR03')` - Информация за община „Белослав“.| Array |
|`Obshtina::getListByOblastName(string)`|Получаване на списък с общините в дадена област, търсена по име. Пример: `Obshtina::getListByOblastName('Благоевград')` - Списък на общините в област „Благоевград“.| Array |
|`Obshtina::getListByOblastCode(string)`|Получаване на списък с общините в дадена област, търсена по трибуквен код (вж. „Пояснение за КОД“). Пример: `Obshtina::getListByOblastCode('SZR')` - Списък на общините в област „Стара Загора“.| Array |
|`Kmetstvo::getList()`|Получаване на списък с всички населени места в Р. България.| Array |
|`Kmetstvo::getByName(string)`|Получаване на информация за дадено кметсво, търсено по име. Пример: `Kmetstvo::getByName('гр. Свищов')` - информация за град Свищов. <br/><br/> *Важно: При търсене НЕ Е нужно да се указва типа на кметството („гр.“, „с.“ и т.н.). Да се има предвид, обаче, че при търсене на кмество САМО по име е възможно резултатите да са повече от един. Например `Kmetstvo::getByName('Разград')` връща информация за град Разград в Североизточна България и за село Разград, Монтанско.*| Array |
|`Kmetstvo::getByCode(string)`|Получаване на информация за дадено кметсво, търсено по идентификационен код (вж. „Пояснение за КОД“). Пример: `Kmetstvo::getByCode('RSE08-003')` - информация за село Ценово, Русенско.| Array |
|`Kmetstvo::getListByObshtinaName(string)`|Получаване на списък с всички кметства в дадена община, търсена по име. Пример: `Kmetstvo::getListByObshtinaName('Стралджа')` - Списък с кметствата в община Стралджа.| Array |
|`Kmetstvo::getListByObshtinaCode(string)`|Получаване на списък с всички кметства в дадена община, търсена по идентификационен код (вж. „Пояснение за КОД“). Пример: `Kmetstvo::getListByObshtinaCode('PAZ03')` - Списък на кметствата в община Брацигово| Array |
|`Kmetstvo::getListByOblastName(string)`|Получаване на списък с всички кметства в дадена област, търсена по име. Пример: `Kmetstvo::getListByOblastName('Търговище')` - Списък с кметствата в област Търговище.| Array |
|`Kmetstvo::getListByOblastCode(string)`|Получаване на списък с всички кметства в дадена област, търсена по трибуквен код (вж. „Пояснение за КОД“). Пример: `Kmetstvo::getListByOblastCode('VRC')` - Списък с кметствата в област Враца.| Array |

#### Пояснение за КОД
Когато става въпрос за области, трибуквеният код идва от екселските файлове на НСИ и представлява трибуквено представяне на всяка област на латиница. Напомня на отговора на „asl pls“ от едно време  (макар и да е всеизвестен факт, че `BS KS`, а `SF RF` ;) ). Например, Старозагорска област е обозначена като „SZR“, Пловдивска като „PDV“, Бургаска като „BGS“, Софийска като „SFO“, София (като отделна област) като „SOF“ и т.н.

##### Ето и таблица с трибуквените кодове на всяка област
|*Трибуквен код*|*Област*|
|--|--|
| *BLG* |*Благоевград*|
| *BGS* |*Бургас*|
| *VAR* |*Варна*|
| *VTR* |*Велико Търново*|
| *VID* |*Видин*|
| *VRC* |*Враца*|
| *GAB* |*Габрово*|
| *DOB* |*Добрич*|
| *KRZ* |*Кърджали*|
| *KNL* |*Кюстендил*|
| *LOV* |*Ловеч*|
| *MON* |*Монтана*|
| *PAZ* |*Пазарджик*|
| *PER* |*Перник*|
| *PVN* |*Плевен*|
| *PDV* |*Пловдив*|
| *RAZ* |*Разград*|
| *RSE* |*Русе*|
| *SLS* |*Силистра*|
| *SLV* |*Сливен*|
| *SML* |*Смолян*|
| *SFO* |*София*|
| *SOF* |*София (столица)*|
| *SZR* |*Стара Загора*|
| *TGV* |*Търговище*|
| *HKV* |*Хасково*|
| *SHU* |*Шумен*|
| *JAM* |*Ямбол*|

Идентификационният код на всяка община е образуван от трибуквения код на областта + поредния номер на общината (по азбучен ред), в две цифри с водеща нула. Например, община Разлог има идентификационен код `BLG08`, а община Якоруда - `BLG14`.
<br/>
По подобен начин стои и въпросът с идентификационните кодове на кметствата, но там за основа е взет кода на общината, в която се намира кметството + тире + поредния номер на самото кметство, в три цифри с водеща нула. Например, град Свищов (на който автора има честта да е горд представител ;) ) се намира на територията на община Свищов, която пък е в област Велико Търново - съответно идентификационния код на гр. Свищов е `VTR08-012`. Или казано по друг начин: дванадесетото поред кметство в осмата поред община във Великотърновска област.

#### Връзка с мен
За въпроси и предложения, пишете ми на [avramov.emil@gmail.com](mailto:avramov.emil@gmail.com). Ако някой може да направи подобрения по кода, нова функционалност или каквато и да била положителна промяна - нека се чувства свободен да направи Pull Request към repo-то :)