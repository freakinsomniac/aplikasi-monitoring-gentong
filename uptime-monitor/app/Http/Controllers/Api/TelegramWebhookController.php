<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Monitor;
use App\Models\Incident;
use App\Models\NotificationChannel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TelegramWebhookController extends Controller
{
    /**
     * Handle incoming webhook from Telegram
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $update = $request->all();
            Log::info('Telegram webhook received', ['update' => $update]);

            // Extract message data
            if (!isset($update['message'])) {
                return response()->json(['ok' => true]);
            }

            $message = $update['message'];
            $chatId = $message['chat']['id'];
            $text = $message['text'] ?? '';
            
            // Handle commands
            if (strpos($text, '/') === 0) {
                $this->handleCommand($chatId, $text);
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['ok' => false], 500);
        }
    }

    /**
     * Handle Telegram commands
     */
    private function handleCommand(string $chatId, string $command): void
    {
        $command = trim(strtolower(explode(' ', $command)[0]));
        
        Log::info('Handling Telegram command', ['chat_id' => $chatId, 'command' => $command]);

        switch ($command) {
            case '/start':
                $this->sendStart($chatId);
                break;
            case '/help':
                $this->sendHelp($chatId);
                break;
            case '/status':
                $this->sendStatus($chatId);
                break;
            case '/incidents':
                $this->sendIncidents($chatId);
                break;
            case '/monitors':
                $this->sendMonitors($chatId);
                break;
            case '/subscribe':
                $this->subscribe($chatId);
                break;
            case '/unsubscribe':
                $this->unsubscribe($chatId);
                break;
            case '/uptime':
                $this->sendUptime($chatId);
                break;
            case '/ping':
                $this->sendPing($chatId);
                break;
            default:
                $this->sendUnknownCommand($chatId);
        }
    }

    private function sendStart(string $chatId): void
    {
        $message = "🤖 *Selamat datang di Uptime Monitor Bot!*\n\n";
        $message .= "Bot ini akan mengirimkan notifikasi otomatis ketika ada service yang down.\n\n";
        $message .= "📋 *Perintah yang tersedia:*\n";
        $message .= "/help - Tampilkan panduan penggunaan\n";
        $message .= "/status - Cek status semua monitor\n";
        $message .= "/incidents - Lihat incident terbaru\n";
        $message .= "/monitors - Daftar semua monitor\n";
        $message .= "/uptime - Statistik uptime\n";
        $message .= "/ping - Cek bot aktif\n\n";
        $message .= "💡 *Chat ID Anda:* `{$chatId}`\n";
        $message .= "Gunakan Chat ID ini untuk setup notifikasi di dashboard.";

        $this->sendMessage($chatId, $message);
    }

    private function sendHelp(string $chatId): void
    {
        $message = "📚 *Panduan Penggunaan Bot*\n\n";
        $message .= "*Perintah Monitoring:*\n";
        $message .= "/status - Lihat status semua monitor aktif\n";
        $message .= "/incidents - Daftar 5 incident terbaru\n";
        $message .= "/monitors - Daftar semua monitor\n";
        $message .= "/uptime - Statistik uptime rata-rata\n\n";
        $message .= "*Perintah Umum:*\n";
        $message .= "/ping - Cek apakah bot aktif\n";
        $message .= "/help - Tampilkan panduan ini\n\n";
        $message .= "💬 Bot akan otomatis mengirim notifikasi saat:\n";
        $message .= "• Service down (incident baru)\n";
        $message .= "• Service up kembali (incident resolved)";

        $this->sendMessage($chatId, $message);
    }

    private function sendStatus(string $chatId): void
    {
        $monitors = Monitor::where('enabled', true)->get();
        
        if ($monitors->isEmpty()) {
            $this->sendMessage($chatId, "⚠️ Tidak ada monitor yang aktif.");
            return;
        }

        $message = "📊 *Status Monitor*\n\n";
        
        $upCount = 0;
        $downCount = 0;
        
        foreach ($monitors as $monitor) {
            $status = $monitor->last_status ?? 'unknown';
            $emoji = $status === 'up' ? '✅' : ($status === 'down' ? '❌' : '⚪');
            
            $message .= "{$emoji} *{$monitor->name}*\n";
            $message .= "   Status: " . strtoupper($status) . "\n";
            $message .= "   Target: {$monitor->target}\n";
            
            if ($monitor->last_checked_at) {
                $lastCheck = \Carbon\Carbon::parse($monitor->last_checked_at)->diffForHumans();
                $message .= "   Last check: {$lastCheck}\n";
            }
            
            $message .= "\n";
            
            if ($status === 'up') $upCount++;
            if ($status === 'down') $downCount++;
        }

        $message .= "━━━━━━━━━━━━━━━━━\n";
        $message .= "Total: {$monitors->count()} monitors\n";
        $message .= "✅ Up: {$upCount} | ❌ Down: {$downCount}";

        $this->sendMessage($chatId, $message);
    }

    private function sendIncidents(string $chatId): void
    {
        $incidents = Incident::with('monitor')
            ->orderBy('started_at', 'desc')
            ->limit(5)
            ->get();

        if ($incidents->isEmpty()) {
            $this->sendMessage($chatId, "✅ Tidak ada incident!");
            return;
        }

        $message = "🚨 *Incident Terbaru*\n\n";

        foreach ($incidents as $incident) {
            $status = $incident->status === 'open' ? '🔴 OPEN' : '✅ RESOLVED';
            $startedAt = \Carbon\Carbon::parse($incident->started_at)->format('d/m/Y H:i');
            
            $message .= "{$status} *{$incident->monitor->name}*\n";
            $message .= "   Started: {$startedAt}\n";
            
            if ($incident->resolved_at) {
                $resolvedAt = \Carbon\Carbon::parse($incident->resolved_at)->format('d/m/Y H:i');
                $duration = \Carbon\Carbon::parse($incident->started_at)
                    ->diffForHumans(\Carbon\Carbon::parse($incident->resolved_at), true);
                $message .= "   Resolved: {$resolvedAt}\n";
                $message .= "   Duration: {$duration}\n";
            }
            
            if ($incident->error_message) {
                $error = substr($incident->error_message, 0, 50);
                $message .= "   Error: {$error}...\n";
            }
            
            $message .= "\n";
        }

        $this->sendMessage($chatId, $message);
    }

    private function sendMonitors(string $chatId): void
    {
        $monitors = Monitor::all();

        if ($monitors->isEmpty()) {
            $this->sendMessage($chatId, "⚠️ Belum ada monitor.");
            return;
        }

        $message = "📋 *Daftar Monitor*\n\n";

        foreach ($monitors as $monitor) {
            $enabled = $monitor->enabled ? '✅' : '⏸️';
            $status = $monitor->last_status ?? 'unknown';
            
            $message .= "{$enabled} *{$monitor->name}*\n";
            $message .= "   Type: " . strtoupper($monitor->type) . "\n";
            $message .= "   Target: {$monitor->target}\n";
            $message .= "   Status: " . strtoupper($status) . "\n";
            $message .= "   Interval: {$monitor->interval_seconds}s\n\n";
        }

        $message .= "Total: {$monitors->count()} monitors";

        $this->sendMessage($chatId, $message);
    }

    private function sendUptime(string $chatId): void
    {
        $monitors = Monitor::where('enabled', true)->get();

        if ($monitors->isEmpty()) {
            $this->sendMessage($chatId, "⚠️ Tidak ada monitor aktif.");
            return;
        }

        $message = "📈 *Statistik Uptime*\n\n";

        $totalUptime = 0;
        $count = 0;

        foreach ($monitors as $monitor) {
            $uptime = $monitor->uptime_percentage ?? 0;
            $emoji = $uptime >= 99 ? '🟢' : ($uptime >= 95 ? '🟡' : '🔴');
            
            $message .= "{$emoji} *{$monitor->name}*\n";
            $message .= "   Uptime: " . number_format($uptime, 2) . "%\n\n";
            
            $totalUptime += $uptime;
            $count++;
        }

        $avgUptime = $count > 0 ? $totalUptime / $count : 0;
        
        $message .= "━━━━━━━━━━━━━━━━━\n";
        $message .= "Rata-rata: " . number_format($avgUptime, 2) . "%";

        $this->sendMessage($chatId, $message);
    }

    private function subscribe(string $chatId): void
    {
        $message = "ℹ️ *Fitur Subscribe*\n\n";
        $message .= "Untuk mengaktifkan notifikasi:\n";
        $message .= "1. Buka dashboard web\n";
        $message .= "2. Masuk ke menu Notification Channels\n";
        $message .= "3. Tambah channel Telegram baru\n";
        $message .= "4. Masukkan Chat ID: `{$chatId}`\n\n";
        $message .= "Setelah itu, Anda akan otomatis menerima notifikasi!";

        $this->sendMessage($chatId, $message);
    }

    private function unsubscribe(string $chatId): void
    {
        $message = "ℹ️ *Fitur Unsubscribe*\n\n";
        $message .= "Untuk menonaktifkan notifikasi:\n";
        $message .= "1. Buka dashboard web\n";
        $message .= "2. Masuk ke menu Notification Channels\n";
        $message .= "3. Disable atau hapus channel dengan Chat ID: `{$chatId}`";

        $this->sendMessage($chatId, $message);
    }

    private function sendPing(string $chatId): void
    {
        $message = "🏓 Pong! Bot aktif dan berjalan.\n\n";
        $message .= "⏰ " . now()->format('d/m/Y H:i:s');

        $this->sendMessage($chatId, $message);
    }

    private function sendUnknownCommand(string $chatId): void
    {
        $message = "❓ Perintah tidak dikenali.\n\n";
        $message .= "Ketik /help untuk melihat daftar perintah yang tersedia.";

        $this->sendMessage($chatId, $message);
    }

    /**
     * Send message to Telegram
     */
    private function sendMessage(string $chatId, string $text): void
    {
        Log::info('Attempting to send Telegram message', ['chat_id' => $chatId, 'text_length' => strlen($text)]);
        
        // Get bot token from first active Telegram channel
        $channel = NotificationChannel::where('type', 'telegram')
            ->where('is_enabled', true)
            ->first();

        if (!$channel) {
            Log::warning('No active Telegram channel found for command response');
            return;
        }

        // Decode config if it's JSON string
        $config = is_string($channel->config) ? json_decode($channel->config, true) : $channel->config;
        $botToken = $config['bot_token'] ?? '';

        if (empty($botToken)) {
            Log::error('Bot token not configured', ['config' => $config]);
            return;
        }

        Log::info('Sending message to Telegram API', ['bot_token_length' => strlen($botToken)]);

        try {
            $response = Http::withOptions(['verify' => false])
                ->timeout(30)
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'Markdown',
                    'disable_web_page_preview' => true,
                ]);

            if (!$response->successful()) {
                Log::error('Failed to send Telegram message', [
                    'chat_id' => $chatId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            } else {
                Log::info('Telegram message sent successfully', ['chat_id' => $chatId]);
            }
        } catch (\Exception $e) {
            Log::error('Telegram send message error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
