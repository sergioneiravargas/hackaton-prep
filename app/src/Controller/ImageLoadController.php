<?php

namespace App\Controller;

use App\Form\ImageLoadFormType;
use OpenAI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageLoadController extends AbstractController
{
    #[Route('/image-load', name: 'app_image_load')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ImageLoadFormType::class);
        $form->handleRequest($request);
        $result = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->askGpt($form->get('checkin')->getData(), $form->get('checkout')->getData());
        }

        return $this->render('image_load/index.html.twig', [
            'form' => $form->createView(),
            'damages' => $result['damages'] ?? null,
        ]); 
    }

    private function askGpt(UploadedFile $checkinImage, UploadedFile $checkoutImage): array
    {
        $client = OpenAI::client('');

        $checkinImageBase64 = base64_encode($checkinImage->getContent());
        $checkoutImageBase64 = base64_encode($checkoutImage->getContent());

        $messages = [
            [
                'role' => 'system', 
                'content' => 'Se agregarán las URL de imágenes asociadas a uno o más elementos de una propiedad durante los procesos de checkin (ántes) y checkout (después).'.
                'Debes acceder y comparar las imágenes'.
                'En caso de existir diferencias, debes indentificar los nuevos daños de la propiedad y/o inmuebles presentes en las imágenes.'.
                'Tu respuesta debe ser un listado incluyendo los daños identificados y una breve descripción.'.
                'Adicionalmente se debe incluir el precio asociado al costo de reparación del elemento asociado.'.
                'El listado de costos para cada reparación es el siguiente:'.
                'pintura exterior (10000 CLP/metro cuadrado), pintura interior (5000 CLP/metro cuadrado), ventana habitación (30000 CLP/unidad), ventanal living (60000 CLP/unidad).'.
                'En caso de que los daños asociados sean a un mismo elemento de la propiedad, debes incluirlos en un solo item.'.
                'En caso de que los daños asociados sean a diferentes elementos de la propiedad, debes incluirlos en items separados.'.
                'El formato es un arreglo JSON de elementos, cada elemento tiene los campos "description" (string con la descripción textual de la respuesta) y "repair_cost" (string con el valor, moneda y forma de cobro asociado al costo, null en caso de no poder identificarlo).'.
                'El nombre del listado de daños en la respuesta es "damages".',
            ],
            [
                'role' => 'user', 
                'content' => [
                    [
                    'type' => 'text',
                    'text' => 'URL de imágen checkin',
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => 'data:image/jpeg;base64,'.$checkinImageBase64,
                        ],
                    ]
                ],
            ],
            [
                'role' => 'user', 
                'content' => [
                    [
                    'type' => 'text',
                    'text' => 'URL de imágen checkout',
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => 'data:image/jpeg;base64,'.$checkoutImageBase64,
                        ],
                    ]
                ],
            ],
        ];

        $result = $client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => $messages,
            'response_format' => [
                'type' => 'json_object',
            ]
        ]);

        return json_decode($result['choices'][0]['message']['content'], true);
    }
}
