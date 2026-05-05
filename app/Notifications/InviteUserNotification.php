<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InviteUserNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly string $companyName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.set', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ], false));

        return (new MailMessage)
            ->subject("Você foi convidado para {$this->companyName}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Você foi convidado para acessar o painel da {$this->companyName}.")
            ->line('Clique no botão abaixo para definir sua senha. O link expira em 24 horas.')
            ->action('Definir senha', $url)
            ->line('Se você não esperava este convite, pode ignorar este e-mail.');
    }
}
