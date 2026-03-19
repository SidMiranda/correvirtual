<?php

    // 4. Gerar o PIX no Mercado Pago
    // Agora usamos o valor real ($registration->price) e não mais 10.00 fixo
    // $pix = MercadoPagoService::createPixPayment(
    //     (float) $registration->price,
    //     auth()->user()->email
    // );

    // 5. Salvar o registro do Pagamento
    // Payment::create([
    //     'registration_id' => $registration->id,
    //     'transaction_id'  => $pix->id,
    //     'qr_code'         => $pix->point_of_interaction->transaction_data->qr_code,
    //     'qr_code_base64'  => $pix->point_of_interaction->transaction_data->qr_code_base64,
    //     'ticket_url'      => $pix->point_of_interaction->transaction_data->ticket_url,
    //     'expires_at'      => $pix->date_of_expiration,
    //     'payload'         => json_encode($pix)
    // ]);

    // 6. Retornar a view com o QR Code
    // Idealmente redirecionamos para uma rota de 'obrigado', mas para MVP isso funciona
    //return view('teste-pix', compact('pix'));