<?php
// app/components/NumeroALetras.php

namespace app\components;

class NumeroALetras
{
    private static $UNIDADES = [
        '',
        'UN',
        'DOS',
        'TRES',
        'CUATRO',
        'CINCO',
        'SEIS',
        'SIETE',
        'OCHO',
        'NUEVE',
        'DIEZ',
        'ONCE',
        'DOCE',
        'TRECE',
        'CATORCE',
        'QUINCE',
        'DIECISEIS',
        'DIECISIETE',
        'DIECIOCHO',
        'DIECINUEVE',
        'VEINTE'
    ];

    private static $DECENAS = [
        'VEINTI',
        'TREINTA',
        'CUARENTA',
        'CINCUENTA',
        'SESENTA',
        'SETENTA',
        'OCHENTA',
        'NOVENTA'
    ];

    private static $CENTENAS = [
        'CIENTO',
        'DOSCIENTOS',
        'TRESCIENTOS',
        'CUATROCIENTOS',
        'QUINIENTOS',
        'SEISCIENTOS',
        'SETECIENTOS',
        'OCHOCIENTOS',
        'NOVECIENTOS'
    ];

    public function convertir($number)
    {
        $number = (int)$number;

        if ($number < 0) {
            return 'MENOS ' . $this->convertir(abs($number));
        }

        if ($number == 0) {
            return 'CERO';
        }

        if ($number == 100) {
            return 'CIEN';
        }

        $resultado = '';

        // Miles
        if ($number >= 1000) {
            $miles = floor($number / 1000);
            $number %= 1000;

            if ($miles == 1) {
                $resultado .= 'MIL';
            } else {
                $resultado .= $this->convertir($miles) . ' MIL';
            }

            if ($number > 0) {
                $resultado .= ' ';
            }
        }

        // Centenas
        if ($number >= 100) {
            $centenas = floor($number / 100);
            $number %= 100;

            if ($centenas == 1 && $number == 0) {
                $resultado .= 'CIEN';
                return $resultado;
            } else {
                $resultado .= self::$CENTENAS[$centenas - 1];
            }

            if ($number > 0) {
                $resultado .= ' ';
            }
        }

        // Decenas y unidades
        if ($number >= 1) {
            if ($number <= 20) {
                $resultado .= self::$UNIDADES[$number];
            } else {
                $decena = floor($number / 10);
                $unidad = $number % 10;

                if ($decena == 2 && $unidad == 1) {
                    $resultado .= 'VEINTIUN';
                } elseif ($decena == 2) {
                    $resultado .= 'VEINTI' . strtolower(self::$UNIDADES[$unidad]);
                } else {
                    $resultado .= self::$DECENAS[$decena - 2];

                    if ($unidad > 0) {
                        $resultado .= ' Y ' . strtolower(self::$UNIDADES[$unidad]);
                    }
                }
            }
        }

        return ucfirst(strtolower($resultado));
    }
}
