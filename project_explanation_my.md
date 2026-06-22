# EcoFit - Gamified Fitness & Nature Project Explanation (မြန်မာဘာသာ)

ဤ project သည် အသုံးပြုသူများ၏ နေ့စဉ်ကိုယ်လက်လှုပ်ရှားမှုများကို ဂိမ်းကစားသကဲ့သို့ ပျော်စရာကောင်းအောင် ပြုလုပ်ထားသော (Gamified) ကျန်းမာရေးနှင့် သဘာဝပတ်ဝန်းကျင်ဆိုင်ရာ Web Application တစ်ခု ဖြစ်ပါသည်။ အသုံးပြုသူများသည် ၎င်းတို့၏ ခြေလှမ်းများ၊ စားသုံးသည့် အစားအစာများနှင့် အိပ်စက်ချိန်များကို မှတ်တမ်းတင်ပြီး ရွှေဒင်္ဂါးအမှတ်များ (Points) စုဆောင်းကာ မိမိတို့၏ ကိုယ်ပိုင် ဥယျာဉ်ခြံမြေကွက်လပ်တွင် သစ်ပင်များကို ဝယ်ယူစိုက်ပျိုးနိုင်မည် ဖြစ်သည်။

---

## ၁။ Project ၏ အဓိက လုပ်ဆောင်ချက်ပုံစံ (Core Concept)

လုပ်ငန်းစဉ်မှာ အောက်ပါအတိုင်း အဆင့်ဆင့် အလုပ်လုပ်ပါသည် -

```mermaid
graph TD
    A[အသုံးပြုသူ ကိုယ်လက်လှုပ်ရှားမှု လုပ်ဆောင်ခြင်း] --> B[Daily Quests & Metrics တင်ခြင်း]
    B --> C[Quest ပြီးမြောက်ပါက Points ရရှိခြင်း]
    C --> D[ရရှိလာသော Points များဖြင့် Tree Shop မှ သစ်ပင်များ ဝယ်ယူခြင်း]
    D --> E[သစ်ပင်များကို မိမိ၏ Virtual Garden Grid တွင် စိုက်ပျိုးခြင်း]
end
```

1. **Daily Quests (နေ့စဉ်တာဝန်များ)**: အသုံးပြုသူတွင် တစ်နေ့တာလုပ်ဆောင်ရမည့် ကိုယ်လက်လှုပ်ရှားမှုများ ရှိမည် (ဥပမာ- ခြေလှမ်း ၅,၀၀၀ လျှောက်ရန်၊ ၂ ကီလိုမီတာ ပြေးရန် စသည်)။
2. **Earn Points (အမှတ်များ စုဆောင်းခြင်း)**: အဆိုပါ Quest များကို ပြီးမြောက်အောင် လုပ်ဆောင်ပြီးပါက သတ်မှတ်ထားသော ရွှေဒင်္ဂါးပြားအမှတ်များ (Points) ရရှိမည်။
3. **Tree Shop (သစ်ပင်စတိုး)**: ရရှိလာသည့် Points များကို သစ်ပင်စတိုးဆိုင်တွင် အသုံးပြု၍ သစ်ပင်အမျိုးမျိုး (ဥပမာ- Oak, Pine, Cherry Blossom စသည်) ကို ဝယ်ယူနိုင်သည်။
4. **Virtual Garden Grid (စိုက်ပျိုးမြေပြင်)**: ဝယ်ယူလိုက်သော သစ်ပင်များကို Dashboard ပေါ်ရှိ $6 \times 6$ စိုက်ပျိုးမြေကွက်လပ်တွင် မိမိကြိုက်နှစ်သက်ရာနေရာကို ရွေးချယ်စိုက်ပျိုးနိုင်ပါသည်။

---

## ၂။ Directory ဖွဲ့စည်းပုံနှင့် ဖိုင်တစ်ခုချင်းစီ၏ တာဝန်များ

Project ၏ တည်ဆောက်ပုံမှာ အောက်ပါအတိုင်း ဖြစ်ပါသည် -

```text
/gamified-fitness-app
│
├── /assets                # Static assets (CSS, JS)
│   ├── /css
│   │   └── style.css      # Custom HSL design tokens, Glassmorphic UI, Breeze animation များနှင့် Light Mode configuration များ
│   └── /js
│       └── app.js         # Interactive JS code (Confetti effect, AJAX requests, Theme/Language cookie switcher)
│
├── /includes              # PHP library များနှင့် Reusable templates များ
│   ├── db.php             # Database connection (PDO) နှင့် session based control helpers များ
│   ├── lang.php           # Multi-language mapping module (ဘာသာစကား ၄ မျိုး)
│   ├── header.php         # Global navigation, Mobile side drawer, desktop responsive headers
│   ├── footer.php         # Footer component များနှင့် core JavaScript dependencies များ
│   └── tree_svgs.php      # သစ်ပင်ပုံများကို responsive SVG codes ဖြင့် dynamic ဆွဲပေးသော module
│
├── /api                   # Back-end AJAX interfaces (JSON data ပြန်လည်ပေးပို့သည့် APIs)
│   ├── update_quest.php   # Quest point updates များကို database တွင် safe ဖြစ်စေရန် atomic transaction များဖြင့် ရေးသွင်းပေးသည်
│   └── buy_tree.php       # Points များကို တွက်ချက်နုတ်ယူပြီး သစ်ပင်အသစ် စိုက်ပျိုးပေးသည့် API
│
├── /uploads/breakfast     # အသုံးပြုသူ တင်လိုက်သော နံနက်စာ ဓာတ်ပုံများကို သိမ်းဆည်းသည့် နေရာ
│
├── index.php              # Login / Register interface (Authentication page)
├── dashboard.php          # နေ့စဉ် quests တိုးတက်မှုနှင့် စိုက်ပျိုးထားသော ဥယျာဉ်ခြံမြေ grid ကို ပြသပေးသော ဗဟိုစာမျက်နှာ
├── steps.php              # ခြေလှမ်းအကွာအဝေး၊ calories လောင်ကျွမ်းမှု တွက်ချက်ပေးပြီး quest update လုပ်ပေးသော စာမျက်နှာ
├── breakfast.php          # နံနက်စာစားသောက်မှုမှတ်တမ်း၊ ဓာတ်ပုံရိုက်တင်ခြင်းနှင့် calories level များကို created_at ဖြင့် dynamic ပြသသော စာမျက်နှာ
├── sleep.php              # အိပ်စက်ချိန်စက်ဝန်း၊ deep/REM sleep analytics များနှင့် recovery ratings များကို ပြသပေးသော စာမျက်နှာ
├── shop.php               # သစ်ပင်အမျိုးအစားများ စာရင်းနှင့် dynamic points balance သုံးပြီး စိုက်ပျိုးရန် သစ်ပင်ဝယ်ယူနိုင်သော store စာမျက်နှာ
└── calendar.php           # အသုံးပြုသူ၏ နေ့စဉ် တစိုက်မတ်မတ် လေ့ကျင့်ခန်းလုပ်ဆောင်မှုများကို ခြေရာခံပြသသော Calendar widget
```

---

## ၃။ အဓိက လုပ်ဆောင်ချက်များ (Key Features)

### (က) ဘာသာစကား (၄) မျိုး ပြောင်းလဲအသုံးပြုနိုင်ခြင်း (Multi-Language Engine)
- **ဘာသာစကားများ**: မြန်မာ (`my`)၊ အင်္ဂလိပ် (`en`)၊ ဂျပန် (`ja`) နှင့် ဗီယက်နမ် (`vi`) တို့ကို support လုပ်ပါသည်။
- **အကောင်အထည်ဖော်ပုံ**: [includes/lang.php](file:///c:/Users/HANWAITUN/OneDrive%20-%20yamaguchigakuen/Desktop/gamified-fitness-app/includes/lang.php) တွင် dictionary keyword map များဖြင့် dynamic content translation ကို script များအတွင်း `__('key_name')` function ဖြင့် auto translation ပေးထားပြီး runtime တွင် session နှင့် သက်တမ်း ၁ နှစ်ရှိသော long-term cookie များဖြင့် ရွေးချယ်မှုကို auto loading ပြုလုပ်ပေးပါသည်။

### (ခ) Dark Mode & Light Mode Theme ပြောင်းလဲနိုင်ခြင်း
- **Theme control**: Tailwind config style layout များကို class level (`html.dark` သို့မဟုတ် `html.light`) ဖြင့် CSS transition variables များသုံးကာ ညင်သာစွာ ပြောင်းလဲပေးပါသည်။
- **Persistence**: User ရွေးချယ်လိုက်သော theme mode အား cookie ဖြင့် သိမ်းဆည်းပေးထားသဖြင့် Page update လုပ်ချိန် သို့မဟုတ် အခြားစာမျက်နှာသို့ သွားချိန်တွင် Screen အဖြူရောင် flashing ဖြစ်ခြင်းကို အပြည့်အဝ ကာကွယ်ပေးပါသည်။

### (ဂ) Steps Tracker Module
- [steps.php](file:///c:/Users/HANWAITUN/OneDrive%20-%20yamaguchigakuen/Desktop/gamified-fitness-app/steps.php) တွင် အသုံးပြုသူမှ နေ့စဉ်ခြေလှမ်းများကို ရိုက်ထည့်လိုက်သည်နှင့် (ခြေလှမ်း ၁ လှမ်းလျှင် ၀.၀၀၀၈ ကီလိုမီတာ နှင့် ၀.၀၄ calories နှုန်းဖြင့်) dynamic calculator သုံးကာ distance နှင့် calories ကို realtime တွက်ချက်ပေးပါသည်။ ၎င်းအချက်အလက်များကို နေ့စဉ် Quest database သို့ dynamic stage updates ပေးပို့ပေးပါသည်။

### (ဃ) Breakfast Tracker (နံနက်စာမှတ်တမ်းနှင့် ပုံတင်စနစ်)
- [breakfast.php](file:///c:/Users/HANWAITUN/OneDrive%20-%20yamaguchigakuen/Desktop/gamified-fitness-app/breakfast.php) တွင် အသုံးပြုသူသည် နံနက်စာ စားသုံးခဲ့သော အစားအစာအမည်၊ ကယ်လိုရီတန်ဖိုးများနှင့်အတူ ဖုန်း/ကွန်ပျူတာ ကင်မရာမှတစ်ဆင့် ဓာတ်ပုံရိုက်ကူးခြင်း (သို့မဟုတ်) ဖိုင်တင်ခြင်း ပြုလုပ်နိုင်ပါသည်။
- တင်လိုက်သော ပုံများကို local directory ဖြစ်သည့် `/uploads/breakfast/` ထဲတွင် standard extension filter များ (JPG, JPEG, PNG, WEBP) နှင့် လုံခြုံရေးအရ size restriction (5MB အထိ) စနစ်များဖြင့် လုံခြုံစွာ upload တင်ပေးပြီး database ထဲတွင် User-id ချိတ်ဆက်ကာ timestamp `created_at` နှင့်တကွ အချိန်နှင့်တစ်ပြေးညီ log charts ပြသပေးပါသည်။

### (င) Sleep Tracker & Quality Score
- [sleep.php](file:///c:/Users/HANWAITUN/OneDrive%20-%20yamaguchigakuen/Desktop/gamified-fitness-app/sleep.php) တွင် အိပ်ရာဝင်ချိန်၊ အိပ်ရာထချိန်နှင့် ၎င်းတို့၏ sleep quality rating များကို ထည့်သွင်းမှတ်တမ်းတင်နိုင်ပြီး sleep logs analytical charts များကို လှပစွာ ပြသပေးပါသည်။

---

## ၄။ Database Architecture (Database ပုံစံနှင့် ပတ်သက်မှုများ)

Database schemas များကို [mysql.sql](file:///c:/Users/HANWAITUN/OneDrive%20-%20yamaguchigakuen/Desktop/gamified-fitness-app/mysql.sql) နှင့် [breakfast_table.sql](file:///c:/Users/HANWAITUN/OneDrive%20-%20yamaguchigakuen/Desktop/gamified-fitness-app/breakfast_table.sql) တွင် အောက်ပါအတိုင်း ဖွဲ့စည်းထားပါသည် -

### Database Tables နှင့် ၎င်းတို့၏ ဆက်သွယ်မှုများ

1. **`users` Table**: User အချက်အလက်များနှင့် ၎င်းတို့ပိုင်ဆိုင်သည့် `total_points` ကို သိမ်းဆည်းသည်။
2. **`daily_quests` Table**: နေ့စဉ်လုပ်ဆောင်ရမည့် quest ပုံစံခွက်များ (ဥပမာ- Target တန်ဖိုး၊ Points reward) ကို သတ်မှတ်သိမ်းဆည်းထားသည်။
3. **`user_quests` Table**: User တစ်ယောက်ချင်းစီ၏ နေ့စဉ် တိုးတက်မှု (progress) နှင့် ပြီးမြောက်ခြင်း ရှိ/မရှိ (`is_completed`) ကို ရက်စွဲအလိုက် စောင့်ကြည့်မှတ်တမ်းတင်သည်။
4. **`tree_shop` Table**: Shop တွင် ဝယ်ယူနိုင်သော သစ်ပင်အမျိုးအစားများ၊ ၎င်းတို့၏ ဈေးနှုန်းနှင့် visual code များကို သတ်မှတ်ပေးထားသည်။
5. **`user_garden` Table**: User တစ်ယောက်စီ ပိုင်ဆိုင်ပြီး စိုက်ပျိုးထားသော သစ်ပင်များနှင့် ၎င်းတို့စိုက်ထားသည့် နေရာသြဒီနိတ် (`x_coordinate`, `y_coordinate`) ကို မှတ်သားပေးသည်။
6. **`breakfast_logs` Table**: User တစ်ယောက်ချင်းစီ တင်လိုက်သော နံနက်စာပုံလမ်းကြောင်း (`image_path`)၊ meal_name၊ calories နှင့် created_at အချိန်တို့ကို dynamic link ဖြင့် ဆက်သွယ်သိမ်းဆည်းပေးသည်။

---

## ၅။ စနစ်ပိုင်းဆိုင်ရာ ပိုမိုကောင်းမွန်အောင် ပြင်ဆင်မှုများ (System Optimization)

### database transaction error ပြဿနာကို ဖြေရှင်းထားပုံ (Transaction Safety)
MySQL setup အချို့တွင် default autocommit တန်ဖိုးပိတ်ထားလျှင် (`autocommit = 0`) PDO သည် SQL query တစ်ခု စတင်သည်နှင့် implicit transaction ကို background ၌ auto logic ဖြင့် စတင်ထားရှိပြီး ဖြစ်တတ်ပါသည်။ ထိုအချိန်မျိုးတွင် explicit transaction ဖြစ်သော `$pdo->beginTransaction()` ကို ထပ်မံခေါ်ဆိုမိပါက "active transaction" error တက်တတ်ပါသည်။ 
၎င်းပြဿနာကို ဖြေရှင်းရန်အတွက် application တစ်ခုလုံးရှိ database update modules များကို transaction မစတင်မီ check helper logic ဖြစ်သော `if (!$pdo->inTransaction())` ဖြင့် လုံခြုံစွာ ဝန်းရံပြင်ဆင်ထားပါသည်။

---

## ၆။ Visual Design နှင့် Animation များအကြောင်း

- **Glassmorphism**: UI တစ်ခုလုံးကို ဆွဲဆောင်မှုရှိစေရန် transparent မှုန်ဝါးဝါးနောက်ခံပုံစံ (`backdrop-filter: blur(12px)`) ဖြင့် ပြင်ဆင်ထားသည်။
- **Breeze Animation**: စိုက်ပျိုးပြီးသော SVG သစ်ပင်များကို တောင့်တောင့်ကြီးမဖြစ်နေစေဘဲ တကယ့်သစ်ပင်များ လေတိုက်၍ ယိမ်းနွဲ့နေသကဲ့သို့ ယိမ်းယိုင်နေသော animation (`transform-origin: bottom center` ဖြင့် လှုပ်ရှားစေခြင်း) ကို CSS `@keyframes breeze` သုံးပြီး ဖန်တီးထားသည်။
- **Toast Notifications**: အမှားအယွင်းများ သို့မဟုတ် အောင်မြင်မှုများကို မျက်နှာပြင်ပေါ်တွင် ယာယီသတင်းစကား (Toast notification box) များအဖြစ် ညာဘက်အောက်ထောင့်တွင် ပြသပေးသည်။
