<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use SergiX44\Nutgram\Nutgram;
use App\Models\Operator;
use App\Models\LunchSetting;
use App\Models\Supervisor;
use Carbon\Carbon;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

$groupChatId = config('telegram.group_chat_id');

// Start komandasi
$bot->onCommand('start', function (Nutgram $bot) {
    $bot->sendMessage('Obed navbati boshqaruv botiga xush kelibsiz!');
});

// Supervizor maksimal obed sonini o‘zgartirishi
$bot->onCommand('setmax {count}', function (Nutgram $bot, $count) {
    $supervisor = Supervisor::where('telegram_id', $bot->chatId())->first();
    if (!$supervisor) {
        $bot->sendMessage('Sizda ruxsat yo‘q.');
        return;
    }

    $setting = LunchSetting::first();
    if (!$setting) {
        $setting = LunchSetting::create(['max_operators' => $count]);
    } else {
        $setting->max_operators = $count;
        $setting->save();
    }

    $bot->sendMessage("Maksimal obed soni $count ta qilib o‘zgartirildi.");
});

// Operator navbatga yozilish
$bot->onCommand('join', function (Nutgram $bot) {
    $telegramId = $bot->userId();
    $operator = Operator::where('telegram_id', $telegramId)->first();

    if (!$operator) {
        $bot->sendMessage('Siz tizimda ro‘yxatdan o‘tmagansiz.');
        return;
    }

    $today = Carbon::today();

    if ($operator->last_lunch_date == $today->toDateString()) {
        $bot->sendMessage('Siz bugun allaqachon navbatga yozilgansiz.');
        return;
    }

    $maxOrder = Operator::whereDate('last_lunch_date', $today)->max('lunch_order');

    $operator->lunch_order = $maxOrder + 1;
    $operator->last_lunch_date = $today->toDateString();
    $operator->status = 'available';
    $operator->save();

    $bot->sendMessage('Siz obed navbatiga muvaffaqiyatli yozildingiz!');
});

// Guruhga navbat yig‘ish uchun e’lon
$bot->onCommand('announce', function (Nutgram $bot) use ($groupChatId) {
    $bot->sendMessage($groupChatId, '⚡️ Kim obed navbatiga yozilishni xohlaydi? Iltimos, shaxsiydan /join deb yozing.');
});

// Navbatni boshlash (supervizor komandasi)
$bot->onCommand('next', function (Nutgram $bot) {
    $supervisor = Supervisor::where('telegram_id', $bot->chatId())->first();
    if (!$supervisor) {
        $bot->sendMessage('Sizda ruxsat yo‘q.');
        return;
    }

    $today = Carbon::today();
    $currentLunch = Operator::where('status', 'at_lunch')->whereDate('last_lunch_date', $today)->count();
    $maxLunch = LunchSetting::first()->max_operators;

    if ($currentLunch >= $maxLunch) {
        $bot->sendMessage('Maksimal obeddagi operatorlar soniga yetildi.');
        return;
    }

    $operator = Operator::where('status', 'available')->whereDate('last_lunch_date', $today)->orderBy('lunch_order')->first();

    if (!$operator) {
        $bot->sendMessage('Navbatdagi operator topilmadi.');
        return;
    }

    // Operatorga shaxsiy xabar yuborish
    $keyboard = InlineKeyboardMarkup::make()
        ->addRow(
            InlineKeyboardButton::make('✅ Obedga chiqish', callback_data: 'confirm_lunch_' . $operator->id)
        );

$bot->sendMessage(
    $operator->telegram_id,
    "Sizning obedga chiqish navbatingiz keldi! Iltimos, tasdiqlang.",
    reply_markup: $keyboard
);

});

// Operator obedga chiqishni tasdiqlaydi
$bot->onCallbackQueryData('confirm_lunch_{operator_id}', function (Nutgram $bot, $operator_id) {
    $operator = Operator::find($operator_id);
    if (!$operator) {
        $bot->sendMessage('Operator topilmadi.');
        return;
    }

    $operator->status = 'at_lunch';
    $operator->save();

    // Supervizorlarga habar yuborish
    $supervisors = Supervisor::all();
    foreach ($supervisors as $supervisor) {
        $bot->sendMessage($supervisor->telegram_id, "✅ {$operator->name} obedga chiqdi.");
    }

    $bot->sendMessage($operator->telegram_id, 'Siz obedga chiqdingiz. Obed tugashiga 5 daqiqa qolganda eslatma beriladi.');

    // Bu yerda 5 daqiqalik eslatmani cron yoki queue orqali yozib qo‘shish kerak
});

// Supervizor bugungi statusni ko‘rishi
$bot->onCommand('status', function (Nutgram $bot) {
    $supervisor = Supervisor::where('telegram_id', $bot->chatId())->first();
    if (!$supervisor) {
        $bot->sendMessage('Sizda ruxsat yo‘q.');
        return;
    }

    $today = Carbon::today();
    $operators = Operator::whereDate('last_lunch_date', $today)->orderBy('lunch_order')->get();

    if ($operators->isEmpty()) {
        $bot->sendMessage('Bugun hali hech kim navbatga yozilmagan.');
        return;
    }

    $text = "📋 Bugungi obed navbati:\n\n";

    foreach ($operators as $operator) {
        $text .= "{$operator->lunch_order}. {$operator->name} - {$operator->status}\n";
    }

    $bot->sendMessage($bot->chatId(), $text);
});
