# EcoFit - Gamified Fitness & Nature Project Explanation (မြန်မာဘာသာ)

ဤ project သည် အသုံးပြုသူများ၏ နေ့စဉ်ကိုယ်လက်လှုပ်ရှားမှုများကို ဂိမ်းကစားသကဲ့သို့ ပျော်စရာကောင်းအောင် ပြုလုပ်ထားသော (Gamified) ကျန်းမာရေးနှင့် သဘာဝပတ်ဝန်းကျင်ဆိုင်ရာ Web Application တစ်ခု ဖြစ်ပါသည်။

---

## ၁။ Project ၏ အဓိက လုပ်ဆောင်ချက်ပုံစံ (Core Concept)

လုပ်ငန်းစဉ်မှာ အောက်ပါအတိုင်း အဆင့်ဆင့် အလုပ်လုပ်ပါသည် -

```mermaid
graph TD
    A[အသုံးပြုသူ ကိုယ်လက်လှုပ်ရှားမှု လုပ်ဆောင်ခြင်း] --> B[Daily Quests Progress တင်ခြင်း]
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

Project folder တည်ဆောက်ပုံမှာ အောက်ပါအတိုင်း ဖြစ်ပါသည် -

```text
/gamified-fitness-app
│
├── /assets                # Static files (CSS, JS)
│   ├── /css
│   │   └── style.css      # Gradient, Glassmorphic UI များနှင့် sway animations များ
│   └── /js
│       └── app.js         # AJAX API triggers၊ Confetti effect နှင့် client-side interactive logic များ
│
├── /includes              # Reusable PHP codes များ
│   ├── db.php             # Database connection (PDO) နှင့် Session Helpers များ
│   ├── header.php         # HTML header ခေါင်းစဉ်နှင့် Navigation bar
│   ├── footer.php         # HTML footer ပိတ်သတ်ချက်နှင့် JS link
│   └── tree_svgs.php      # သစ်ပင်ပုံများကို responsive SVG markup ဖြင့် ဆွဲပေးသော file
│
├── /api                   # Web API endpoints (JSON return ပေးသော ဖိုင်များ)
│   ├── update_quest.php   # Quest တိုးတက်မှုကို MySQL တွင် save ပြီး Point ပေးသော API
│   └── buy_tree.php       # Point နုတ်ပြီး သစ်ပင်စိုက်ပျိုးပေးသော API
│
├── index.php              # စတင်ဝင်ရောက်မည့် စာမျက်နှာ (Login / Registration)
├── dashboard.php          # Quests များနှင့် Garden စိုက်ပျိုးမြေပြင်ကို ပြသပေးသော Dashboard
└── shop.php               # သစ်ပင်များစာရင်းနှင့် ဈေးနှုန်းများကို ပြသပေးသော Store
```

---

## ၃။ Database ပုံစံနှင့် ပတ်သက်မှုများ (Database Architecture)

Database table ၅ ခုသည် တစ်ခုနှင့်တစ်ခု အောက်ပါအတိုင်း ချိတ်ဆက်ထားပါသည် -

* **`users` Table**: User အချက်အလက်များနှင့် လက်ရှိစုဆောင်းမိထားသော point စုစုပေါင်း (`total_points`) ကို သိမ်းဆည်းသည်။
* **`daily_quests` Table**: နေ့စဉ်လုပ်ဆောင်ရမည့် quest ပုံစံခွက်များ (ဥပမာ- Target တန်ဖိုး၊ Points reward) ကို သတ်မှတ်သိမ်းဆည်းထားသည်။
* **`user_quests` Table**: User တစ်ယောက်ချင်းစီ၏ နေ့စဉ် တိုးတက်မှု (progress) နှင့် ပြီးမြောက်ခြင်း ရှိ/မရှိ (`is_completed`) ကို ရက်စွဲအလိုက် စောင့်ကြည့်မှတ်တမ်းတင်သည်။
* **`tree_shop` Table**: Shop တွင် ဝယ်ယူနိုင်သော သစ်ပင်အမျိုးအစားများ၊ ၎င်းတို့၏ ဈေးနှုန်းနှင့် visual code များကို သတ်မှတ်ပေးထားသည်။
* **`user_garden` Table**: User တစ်ယောက်စီ ပိုင်ဆိုင်ပြီး စိုက်ပျိုးထားသော သစ်ပင်များနှင့် ၎င်းတို့စိုက်ထားသည့် နေရာသြဒီနိတ် (`x_coordinate`, `y_coordinate`) ကို မှတ်သားပေးသည်။

---

## ၄။ အဓိက လုပ်ဆောင်ချက်များ၏ ကုဒ်အလုပ်လုပ်ပုံ (Core Logic Flow)

### (က) Quest progress အပ်ဒိတ်လုပ်ခြင်းနှင့် Point ပေးခြင်း (Quest Completion)
1. User က Dashboard ပေါ်တွင် ၎င်း၏တိုးတက်မှု (ဥပမာ- ပြေးပြီးသော အကွာအဝေး) ကို ရိုက်ထည့်ပြီး "Update" ကို နှိပ်သည်။
2. Javascript `app.js` မှတစ်ဆင့် POST Request ဖြင့် [api/update_quest.php](file:///c:/Users/HANWAITUN/OneDrive%20-%20yamaguchigakuen/Desktop/gamified-fitness-app/api/update_quest.php) သို့ ပေးပို့သည်။
3. PHP ဘက်တွင် `PDO Prepared Statement` ကိုသုံး၍ **SQL Injection ကို ကာကွယ်**ပြီး တန်ဖိုးများကို စစ်ဆေးသည်။
4. လက်ရှိ progress သည် target တန်ဖိုးထက် ပြည့်မီသွားပါက `MySQL Transaction` ကို စတင်ပါသည် -
   - `user_quests` table တွင် Quest ကို `is_completed = 1` ပြောင်းလဲသည်။
   - `users` table တွင် User ၏ `total_points` ကို သတ်မှတ်ထားသော point အတိုင်း တိုးမြှင့်ပေးသည်။
5. လုပ်ငန်းစဉ်အောင်မြင်ပါက Client ဆီသို့ JSON format ဖြင့် အကြောင်းပြန်ပေးပြီး Javascript မှ **Confetti (အောင်ပွဲခံပန်းပွင့်လေးများ) ဖြန်းပေးခြင်း** နှင့် dynamic points update ကို visual ပြသပေးသည်။

### (ခ) သစ်ပင်ဝယ်ယူခြင်းနှင့် စိုက်ပျိုးခြင်း (Tree Purchasing)
1. User သည် Dashboard ပေါ်ရှိ စိုက်ပျိုးမြေကွက်လပ် ($6 \times 6$ grid) မှ လွတ်နေသော နေရာတစ်ခုခုကို နှိပ်သည်။
2. ထိုအခါ quick-plant overlay ပေါ်လာပြီး ဝယ်ယူလိုသည့်သစ်ပင်ကို ရွေးချယ်ကာ "Plant Tree" ကို နှိပ်သည်။
3. ၎င်းသည် [api/buy_tree.php](file:///c:/Users/HANWAITUN/OneDrive%20-%20yamaguchigakuen/Desktop/gamified-fitness-app/api/buy_tree.php) သို့ API လှမ်းခေါ်သည်။
4. PHP ဘက်မှ စစ်ဆေးမှုများ ပြုလုပ်သည် -
   - ရွေးချယ်ထားသော grid coordinate `(x, y)` တွင် အခြားသစ်ပင် ရှိမနေကြောင်း စစ်ဆေးသည်။
   - User တွင် သစ်ပင်ဖိုး လုံလောက်သော points ရှိ/မရှိ စစ်ဆေးသည်။
5. အားလုံးကိုက်ညီပါက `MySQL Transaction` ဖြင့် -
   - `users` table ထဲရှိ user ၏ point ထဲမှ သစ်ပင်ဖိုးကို နှုတ်ယူသည်။
   - `user_garden` table ထဲသို့ သစ်ပင်အသစ် စိုက်ပျိုးမှုမှတ်တမ်းကို `x_coordinate` နှင့် `y_coordinate` သတ်မှတ်ချက်ဖြင့် ထည့်သွင်းသည်။
6. အောင်မြင်ပါက Browser ပေါ်တွင် Page reload မလုပ်ဘဲ ချက်ချင်းပင် HTML အကွက်ထဲသို့ **သက်ဆိုင်ရာ SVG သစ်ပင်ပုံရိပ်ကို ထည့်သွင်းပေးပြီး** points badge ကို update ပြုလုပ်သည်။

---

## ၅။ Visual Design နှင့် Animation များအကြောင်း

- **Glassmorphism**: UI တစ်ခုလုံးကို ဆွဲဆောင်မှုရှိစေရန် transparent မှုန်ဝါးဝါးနောက်ခံပုံစံ (`backdrop-filter: blur(12px)`) ဖြင့် ပြင်ဆင်ထားသည်။
- **Breeze Animation**: စိုက်ပျိုးပြီးသော SVG သစ်ပင်များကို တောင့်တောင့်ကြီးမဖြစ်နေစေဘဲ တကယ့်သစ်ပင်များ လေတိုက်၍ ယိမ်းနွဲ့နေသကဲ့သို့ ယိမ်းယိုင်နေသော animation (`transform-origin: bottom center` ဖြင့် လှုပ်ရှားစေခြင်း) ကို CSS `@keyframes breeze` သုံးပြီး ဖန်တီးထားသည်။
- **Toast Notifications**: အမှားအယွင်းများ သို့မဟုတ် အောင်မြင်မှုများကို မျက်နှာပြင်ပေါ်တွင် ယာယီသတင်းစကား (Toast notification box) များအဖြစ် ညာဘက်အောက်ထောင့်တွင် ပြသပေးသည်။
