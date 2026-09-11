<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\components\UserHelper;

class EventoReportSearch extends Model
{
    public $date_range_type;
    public $date_from;
    public $date_to;

    // Date range constants
    const DATE_RANGE_CUSTOM = 'custom';
    const DATE_RANGE_TODAY = 'today';
    const DATE_RANGE_YESTERDAY = 'yesterday';
    const DATE_RANGE_THIS_WEEK = 'this_week';
    const DATE_RANGE_LAST_WEEK = 'last_week';
    const DATE_RANGE_THIS_MONTH = 'this_month';
    const DATE_RANGE_LAST_MONTH = 'last_month';
    const DATE_RANGE_LAST_30_DAYS = 'last_30_days';
    const DATE_RANGE_LAST_90_DAYS = 'last_90_days';
    const DATE_RANGE_THIS_YEAR = 'this_year';
    const DATE_RANGE_LAST_YEAR = 'last_year';
    const DATE_RANGE_SINCE_INCIDENTS = 'since_incidents';

    /**
     * Official SUDEASEG currency codes (Table 3 in circular)
     */
    private static $currencyMap = [
        '00' => '00', // Other
        '01' => '01', // Bolívares
        '02' => '02', // Dólares
        '03' => '03', // Euros
    ];

    /**
     * Official SUDEASEG branch codes (Table 4 in circular)
     */
    private static $ramoMap = [
        '01' => '01', // Servicios Médicos Asistenciales
    ];

    /**
     * Official SUDEASEG status codes (Table 7 in circular)
     * For PSNP: All claims in SisSiniestro are "Notificado" (01)
     */
    private static $statusMap = [
        '01' => '01', // Notificado
        '02' => '02', // Pendiente
        '03' => '03', // Parcialmente Pagado
        '04' => '04', // Pagado
        '05' => '05', // Rechazado
    ];

    /**
     * Geographic Location mapping (Table 2 in circular)
     * SINGLE CODE for each Estado + Municipio combination
     * Format: 'CODE' => ['estado' => 'Estado Name', 'municipio' => 'Municipio Name']
     */
    private static $locationMap = [
        // Distrito Capital
        '01' => ['estado' => 'Distrito Capital', 'municipio' => 'Libertador'],

        // Amazonas
        '02' => ['estado' => 'Amazonas', 'municipio' => 'Autónomo Alto Orinoco'],
        '03' => ['estado' => 'Amazonas', 'municipio' => 'Autónomo Atabapo'],
        '04' => ['estado' => 'Amazonas', 'municipio' => 'Autónomo Atures'],
        '05' => ['estado' => 'Amazonas', 'municipio' => 'Autónomo Autana'],
        '06' => ['estado' => 'Amazonas', 'municipio' => 'Autónomo Maroa'],
        '07' => ['estado' => 'Amazonas', 'municipio' => 'Autónomo Manapiare'],
        '08' => ['estado' => 'Amazonas', 'municipio' => 'Autónomo Rio Negro'],

        // Anzoátegui
        '09' => ['estado' => 'Anzoátegui', 'municipio' => 'Anaco'],
        '10' => ['estado' => 'Anzoátegui', 'municipio' => 'Aragua'],
        '11' => ['estado' => 'Anzoátegui', 'municipio' => 'Fernando De Peñalver'],
        '12' => ['estado' => 'Anzoátegui', 'municipio' => 'Francisco Del Carmen Carvajal'],
        '13' => ['estado' => 'Anzoátegui', 'municipio' => 'Francisco De Miranda'],
        '14' => ['estado' => 'Anzoátegui', 'municipio' => 'Guanta'],
        '15' => ['estado' => 'Anzoátegui', 'municipio' => 'Independencia'],
        '16' => ['estado' => 'Anzoátegui', 'municipio' => 'Juan Antonio Sotillo'],
        '17' => ['estado' => 'Anzoátegui', 'municipio' => 'Juan Manuel Cajigal'],
        '18' => ['estado' => 'Anzoátegui', 'municipio' => 'José Gregorio Monagas'],
        '19' => ['estado' => 'Anzoátegui', 'municipio' => 'Libertad'],
        '20' => ['estado' => 'Anzoátegui', 'municipio' => 'Manuel Ezequiel Bruzual'],
        '21' => ['estado' => 'Anzoátegui', 'municipio' => 'Pedro María Freites'],
        '22' => ['estado' => 'Anzoátegui', 'municipio' => 'Piritu'],
        '23' => ['estado' => 'Anzoátegui', 'municipio' => 'San José De Guanipa'],
        '24' => ['estado' => 'Anzoátegui', 'municipio' => 'San Juan De Capistrano'],
        '25' => ['estado' => 'Anzoátegui', 'municipio' => 'Santa Ana'],
        '26' => ['estado' => 'Anzoátegui', 'municipio' => 'Simón Bolívar'],
        '27' => ['estado' => 'Anzoátegui', 'municipio' => 'Simón Rodríguez'],
        '28' => ['estado' => 'Anzoátegui', 'municipio' => 'Sir Arthur Mc Gregor'],
        '29' => ['estado' => 'Anzoátegui', 'municipio' => 'Turístico Diego Bautista Urbaneja'],

        // Apure
        '30' => ['estado' => 'Apure', 'municipio' => 'Achaguas'],
        '31' => ['estado' => 'Apure', 'municipio' => 'Biruaca'],
        '32' => ['estado' => 'Apure', 'municipio' => 'Muñoz'],
        '33' => ['estado' => 'Apure', 'municipio' => 'Páez'],
        '34' => ['estado' => 'Apure', 'municipio' => 'Pedro Camejo'],
        '35' => ['estado' => 'Apure', 'municipio' => 'Rómulo Gallegos'],
        '36' => ['estado' => 'Apure', 'municipio' => 'San Fernando'],

        // Aragua
        '37' => ['estado' => 'Aragua', 'municipio' => 'Bolívar'],
        '38' => ['estado' => 'Aragua', 'municipio' => 'Camatagua'],
        '39' => ['estado' => 'Aragua', 'municipio' => 'Girardot'],
        '40' => ['estado' => 'Aragua', 'municipio' => 'José Ángel Lamas'],
        '41' => ['estado' => 'Aragua', 'municipio' => 'José Félix Ribas'],
        '42' => ['estado' => 'Aragua', 'municipio' => 'José Rafael Revenga'],
        '43' => ['estado' => 'Aragua', 'municipio' => 'Libertador'],
        '44' => ['estado' => 'Aragua', 'municipio' => 'Mario Briceño Iragorry'],
        '45' => ['estado' => 'Aragua', 'municipio' => 'San Casimiro'],
        '46' => ['estado' => 'Aragua', 'municipio' => 'San Sebastián'],
        '47' => ['estado' => 'Aragua', 'municipio' => 'Santiago Mariño'],
        '48' => ['estado' => 'Aragua', 'municipio' => 'Santos Michelena'],
        '49' => ['estado' => 'Aragua', 'municipio' => 'Sucre'],
        '50' => ['estado' => 'Aragua', 'municipio' => 'Tovar'],
        '51' => ['estado' => 'Aragua', 'municipio' => 'Urdaneta'],
        '52' => ['estado' => 'Aragua', 'municipio' => 'Zamora'],
        '53' => ['estado' => 'Aragua', 'municipio' => 'Francisco Linares Alcántara'],
        '54' => ['estado' => 'Aragua', 'municipio' => 'Ocumare De La Costa De Oro'],

        // Barinas
        '55' => ['estado' => 'Barinas', 'municipio' => 'Alberto Arvelo Torrealba'],
        '56' => ['estado' => 'Barinas', 'municipio' => 'Antonio José De Sucre'],
        '57' => ['estado' => 'Barinas', 'municipio' => 'Arismendi'],
        '58' => ['estado' => 'Barinas', 'municipio' => 'Barinas'],
        '59' => ['estado' => 'Barinas', 'municipio' => 'Bolívar'],
        '60' => ['estado' => 'Barinas', 'municipio' => 'Cruz Paredes'],
        '61' => ['estado' => 'Barinas', 'municipio' => 'Ezequiel Zamora'],
        '62' => ['estado' => 'Barinas', 'municipio' => 'Obispos'],
        '63' => ['estado' => 'Barinas', 'municipio' => 'Pedraza'],
        '64' => ['estado' => 'Barinas', 'municipio' => 'Rojas'],
        '65' => ['estado' => 'Barinas', 'municipio' => 'Sosa'],
        '66' => ['estado' => 'Barinas', 'municipio' => 'Andrés Eloy Blanco'],

        // Bolívar
        '67' => ['estado' => 'Bolívar', 'municipio' => 'Caroní'],
        '68' => ['estado' => 'Bolívar', 'municipio' => 'Cedeño'],
        '69' => ['estado' => 'Bolívar', 'municipio' => 'El Callao'],
        '70' => ['estado' => 'Bolívar', 'municipio' => 'Gran Sabana'],
        '71' => ['estado' => 'Bolívar', 'municipio' => 'Heres'],
        '72' => ['estado' => 'Bolívar', 'municipio' => 'Piar'],
        '73' => ['estado' => 'Bolívar', 'municipio' => 'Bolivariano Angostura'],
        '74' => ['estado' => 'Bolívar', 'municipio' => 'Roscio'],
        '75' => ['estado' => 'Bolívar', 'municipio' => 'Sifontes'],
        '76' => ['estado' => 'Bolívar', 'municipio' => 'Sucre'],
        '77' => ['estado' => 'Bolívar', 'municipio' => 'Padre Pedro Chien'],

        // Carabobo
        '78' => ['estado' => 'Carabobo', 'municipio' => 'Bejuma'],
        '79' => ['estado' => 'Carabobo', 'municipio' => 'Carlos Arvelo'],
        '80' => ['estado' => 'Carabobo', 'municipio' => 'Diego Ibarra'],
        '81' => ['estado' => 'Carabobo', 'municipio' => 'Guacara'],
        '82' => ['estado' => 'Carabobo', 'municipio' => 'Juan José Mora'],
        '83' => ['estado' => 'Carabobo', 'municipio' => 'Libertador'],
        '84' => ['estado' => 'Carabobo', 'municipio' => 'Los Guayos'],
        '85' => ['estado' => 'Carabobo', 'municipio' => 'Miranda'],
        '86' => ['estado' => 'Carabobo', 'municipio' => 'Montalbán'],
        '87' => ['estado' => 'Carabobo', 'municipio' => 'Naguanagua'],
        '88' => ['estado' => 'Carabobo', 'municipio' => 'Puerto Cabello'],
        '89' => ['estado' => 'Carabobo', 'municipio' => 'San Diego'],
        '90' => ['estado' => 'Carabobo', 'municipio' => 'San Joaquín'],
        '91' => ['estado' => 'Carabobo', 'municipio' => 'Valencia'],

        // Cojedes
        '92' => ['estado' => 'Cojedes', 'municipio' => 'Anzoátegui'],
        '93' => ['estado' => 'Cojedes', 'municipio' => 'Tinaquillo'],
        '94' => ['estado' => 'Cojedes', 'municipio' => 'Girardot'],
        '95' => ['estado' => 'Cojedes', 'municipio' => 'Lima Blanco'],
        '96' => ['estado' => 'Cojedes', 'municipio' => 'Pao De San Juan Bautista'],
        '97' => ['estado' => 'Cojedes', 'municipio' => 'Ricaurte'],
        '98' => ['estado' => 'Cojedes', 'municipio' => 'Rómulo Gallegos'],
        '99' => ['estado' => 'Cojedes', 'municipio' => 'Ezequiel Zamora'],
        '100' => ['estado' => 'Cojedes', 'municipio' => 'Tinaco'],

        // Delta Amacuro
        '101' => ['estado' => 'Delta Amacuro', 'municipio' => 'Antonio Díaz'],
        '102' => ['estado' => 'Delta Amacuro', 'municipio' => 'Casacoima'],
        '103' => ['estado' => 'Delta Amacuro', 'municipio' => 'Pedernales'],
        '104' => ['estado' => 'Delta Amacuro', 'municipio' => 'Tucupita'],

        // Falcón
        '105' => ['estado' => 'Falcón', 'municipio' => 'Acosta'],
        '106' => ['estado' => 'Falcón', 'municipio' => 'Bolívar'],
        '107' => ['estado' => 'Falcón', 'municipio' => 'Buchivacao'],
        '108' => ['estado' => 'Falcón', 'municipio' => 'Cacique Manuare'],
        '109' => ['estado' => 'Falcón', 'municipio' => 'Carirubana'],
        '110' => ['estado' => 'Falcón', 'municipio' => 'Colina'],
        '111' => ['estado' => 'Falcón', 'municipio' => 'Dabajuro'],
        '112' => ['estado' => 'Falcón', 'municipio' => 'Democracia'],
        '113' => ['estado' => 'Falcón', 'municipio' => 'Falcón'],
        '114' => ['estado' => 'Falcón', 'municipio' => 'Federación'],
        '115' => ['estado' => 'Falcón', 'municipio' => 'Jacura'],
        '116' => ['estado' => 'Falcón', 'municipio' => 'Los Taques'],
        '117' => ['estado' => 'Falcón', 'municipio' => 'Mauroa'],
        '118' => ['estado' => 'Falcón', 'municipio' => 'Miranda'],
        '119' => ['estado' => 'Falcón', 'municipio' => 'Monseñor Iturriza'],
        '120' => ['estado' => 'Falcón', 'municipio' => 'Palmasola'],
        '121' => ['estado' => 'Falcón', 'municipio' => 'Petit'],
        '122' => ['estado' => 'Falcón', 'municipio' => 'Piritu'],
        '123' => ['estado' => 'Falcón', 'municipio' => 'San Francisco'],
        '124' => ['estado' => 'Falcón', 'municipio' => 'Silva'],
        '125' => ['estado' => 'Falcón', 'municipio' => 'Sucre'],
        '126' => ['estado' => 'Falcón', 'municipio' => 'Tocopero'],
        '127' => ['estado' => 'Falcón', 'municipio' => 'Unión'],
        '128' => ['estado' => 'Falcón', 'municipio' => 'Urumaco'],
        '129' => ['estado' => 'Falcón', 'municipio' => 'Zamora'],

        // Guárico
        '130' => ['estado' => 'Guárico', 'municipio' => 'Camaguán'],
        '131' => ['estado' => 'Guárico', 'municipio' => 'Chaguaramas'],
        '132' => ['estado' => 'Guárico', 'municipio' => 'El Socorro'],
        '133' => ['estado' => 'Guárico', 'municipio' => 'San Gerónimo De Guayabal'],
        '134' => ['estado' => 'Guárico', 'municipio' => 'Leonardo Infante'],
        '135' => ['estado' => 'Guárico', 'municipio' => 'Las Mercedes'],
        '136' => ['estado' => 'Guárico', 'municipio' => 'Julián Mellado'],
        '137' => ['estado' => 'Guárico', 'municipio' => 'Francisco De Miranda'],
        '138' => ['estado' => 'Guárico', 'municipio' => 'José Tadeo Monagas'],
        '139' => ['estado' => 'Guárico', 'municipio' => 'Ortiz'],
        '140' => ['estado' => 'Guárico', 'municipio' => 'José Félix Rivas'],
        '141' => ['estado' => 'Guárico', 'municipio' => 'Juan German Roscio'],
        '142' => ['estado' => 'Guárico', 'municipio' => 'San José De Guaribe'],
        '143' => ['estado' => 'Guárico', 'municipio' => 'Santa María De Ipiire'],
        '144' => ['estado' => 'Guárico', 'municipio' => 'Pedro Zaraza'],

        // Lara
        '145' => ['estado' => 'Lara', 'municipio' => 'Andrés Eloy Blanco'],
        '146' => ['estado' => 'Lara', 'municipio' => 'Crespo'],
        '147' => ['estado' => 'Lara', 'municipio' => 'Iribarren'],
        '148' => ['estado' => 'Lara', 'municipio' => 'Jiménez'],
        '149' => ['estado' => 'Lara', 'municipio' => 'Moran'],
        '150' => ['estado' => 'Lara', 'municipio' => 'Palavecino'],
        '151' => ['estado' => 'Lara', 'municipio' => 'Simón Planas'],
        '152' => ['estado' => 'Lara', 'municipio' => 'Torres'],
        '153' => ['estado' => 'Lara', 'municipio' => 'Urdaneta'],

        // Mérida
        '154' => ['estado' => 'Mérida', 'municipio' => 'Alberto Adriani'],
        '155' => ['estado' => 'Mérida', 'municipio' => 'Andrés Bello'],
        '156' => ['estado' => 'Mérida', 'municipio' => 'Antonio Pinto Salinas'],
        '157' => ['estado' => 'Mérida', 'municipio' => 'Aricagua'],
        '158' => ['estado' => 'Mérida', 'municipio' => 'Arzobispo Chacón'],
        '159' => ['estado' => 'Mérida', 'municipio' => 'Campo Elías'],
        '160' => ['estado' => 'Mérida', 'municipio' => 'Caracciolo Parra Olmedo'],
        '161' => ['estado' => 'Mérida', 'municipio' => 'Cardenal Quintero'],
        '162' => ['estado' => 'Mérida', 'municipio' => 'Guaraque'],
        '163' => ['estado' => 'Mérida', 'municipio' => 'Julio César Salas'],
        '164' => ['estado' => 'Mérida', 'municipio' => 'Justo Briceño'],
        '165' => ['estado' => 'Mérida', 'municipio' => 'Libertador'],
        '166' => ['estado' => 'Mérida', 'municipio' => 'Miranda'],
        '167' => ['estado' => 'Mérida', 'municipio' => 'Obispo Ramos De Lora'],
        '168' => ['estado' => 'Mérida', 'municipio' => 'Padre Noguera'],
        '169' => ['estado' => 'Mérida', 'municipio' => 'Pueblo Llano'],
        '170' => ['estado' => 'Mérida', 'municipio' => 'Rangel'],
        '171' => ['estado' => 'Mérida', 'municipio' => 'Rivas Dávila'],
        '172' => ['estado' => 'Mérida', 'municipio' => 'Santos Marquina'],
        '173' => ['estado' => 'Mérida', 'municipio' => 'Sucre'],
        '174' => ['estado' => 'Mérida', 'municipio' => 'Tovar'],
        '175' => ['estado' => 'Mérida', 'municipio' => 'Tulio Febres Cordero'],
        '176' => ['estado' => 'Mérida', 'municipio' => 'Zea'],

        // Miranda
        '177' => ['estado' => 'Miranda', 'municipio' => 'Acevedo'],
        '178' => ['estado' => 'Miranda', 'municipio' => 'Andrés Bello'],
        '179' => ['estado' => 'Miranda', 'municipio' => 'Baruta'],
        '180' => ['estado' => 'Miranda', 'municipio' => 'Brión'],
        '181' => ['estado' => 'Miranda', 'municipio' => 'Buroz'],
        '182' => ['estado' => 'Miranda', 'municipio' => 'Carrizal'],
        '183' => ['estado' => 'Miranda', 'municipio' => 'Chacao'],
        '184' => ['estado' => 'Miranda', 'municipio' => 'Cristóbal Rojas'],
        '185' => ['estado' => 'Miranda', 'municipio' => 'El Hatillo'],
        '186' => ['estado' => 'Miranda', 'municipio' => 'Guaicaipuro'],
        '187' => ['estado' => 'Miranda', 'municipio' => 'Independencia'],
        '188' => ['estado' => 'Miranda', 'municipio' => 'Lander'],
        '189' => ['estado' => 'Miranda', 'municipio' => 'Los Salias'],
        '190' => ['estado' => 'Miranda', 'municipio' => 'Páez'],
        '191' => ['estado' => 'Miranda', 'municipio' => 'Paz Castillo'],
        '192' => ['estado' => 'Miranda', 'municipio' => 'Pedro Gual'],
        '193' => ['estado' => 'Miranda', 'municipio' => 'Plaza'],
        '194' => ['estado' => 'Miranda', 'municipio' => 'Simón Bolívar'],
        '195' => ['estado' => 'Miranda', 'municipio' => 'Sucre'],
        '196' => ['estado' => 'Miranda', 'municipio' => 'Urdaneta'],
        '197' => ['estado' => 'Miranda', 'municipio' => 'Zamora'],

        // Monagas
        '198' => ['estado' => 'Monagas', 'municipio' => 'Acosta'],
        '199' => ['estado' => 'Monagas', 'municipio' => 'Aguasay'],
        '200' => ['estado' => 'Monagas', 'municipio' => 'Bolívar'],
        '201' => ['estado' => 'Monagas', 'municipio' => 'Caripe'],
        '202' => ['estado' => 'Monagas', 'municipio' => 'Cedeño'],
        '203' => ['estado' => 'Monagas', 'municipio' => 'Ezequiel Zamora'],
        '204' => ['estado' => 'Monagas', 'municipio' => 'Libertador'],
        '205' => ['estado' => 'Monagas', 'municipio' => 'Maturín'],
        '206' => ['estado' => 'Monagas', 'municipio' => 'Piar'],
        '207' => ['estado' => 'Monagas', 'municipio' => 'Punceres'],
        '208' => ['estado' => 'Monagas', 'municipio' => 'Santa Bárbara'],
        '209' => ['estado' => 'Monagas', 'municipio' => 'Sotillo'],
        '210' => ['estado' => 'Monagas', 'municipio' => 'Uracoa'],

        // Nueva Esparta
        '211' => ['estado' => 'Nueva Esparta', 'municipio' => 'Antolin Del Campo'],
        '212' => ['estado' => 'Nueva Esparta', 'municipio' => 'Arismendi'],
        '213' => ['estado' => 'Nueva Esparta', 'municipio' => 'Diaz'],
        '214' => ['estado' => 'Nueva Esparta', 'municipio' => 'Garcia'],
        '215' => ['estado' => 'Nueva Esparta', 'municipio' => 'Gomez'],
        '216' => ['estado' => 'Nueva Esparta', 'municipio' => 'Maneiro'],
        '217' => ['estado' => 'Nueva Esparta', 'municipio' => 'Marcano'],
        '218' => ['estado' => 'Nueva Esparta', 'municipio' => 'Mariño'],
        '219' => ['estado' => 'Nueva Esparta', 'municipio' => 'Península De Macanao'],
        '220' => ['estado' => 'Nueva Esparta', 'municipio' => 'Tubores'],
        '221' => ['estado' => 'Nueva Esparta', 'municipio' => 'Villalba'],

        // Portuguesa
        '222' => ['estado' => 'Portuguesa', 'municipio' => 'Agua Blanca'],
        '223' => ['estado' => 'Portuguesa', 'municipio' => 'Araure'],
        '224' => ['estado' => 'Portuguesa', 'municipio' => 'Esteller'],
        '225' => ['estado' => 'Portuguesa', 'municipio' => 'Guanare'],
        '226' => ['estado' => 'Portuguesa', 'municipio' => 'Guanarito'],
        '227' => ['estado' => 'Portuguesa', 'municipio' => 'Monseñor José Vicente De Unda'],
        '228' => ['estado' => 'Portuguesa', 'municipio' => 'Ospino'],
        '229' => ['estado' => 'Portuguesa', 'municipio' => 'Páez'],
        '230' => ['estado' => 'Portuguesa', 'municipio' => 'Papelón'],
        '231' => ['estado' => 'Portuguesa', 'municipio' => 'San Genaro De Boconito'],
        '232' => ['estado' => 'Portuguesa', 'municipio' => 'San Rafael De Onoto'],
        '233' => ['estado' => 'Portuguesa', 'municipio' => 'Santa Rosalía'],
        '234' => ['estado' => 'Portuguesa', 'municipio' => 'Sucre'],
        '235' => ['estado' => 'Portuguesa', 'municipio' => 'Turen'],

        // Sucre
        '236' => ['estado' => 'Sucre', 'municipio' => 'Andrés Eloy Blanco'],
        '237' => ['estado' => 'Sucre', 'municipio' => 'Andrés Mata'],
        '238' => ['estado' => 'Sucre', 'municipio' => 'Arismendi'],
        '239' => ['estado' => 'Sucre', 'municipio' => 'Benítez'],
        '240' => ['estado' => 'Sucre', 'municipio' => 'Bermúdez'],
        '241' => ['estado' => 'Sucre', 'municipio' => 'Bolívar'],
        '242' => ['estado' => 'Sucre', 'municipio' => 'Cajigal'],
        '243' => ['estado' => 'Sucre', 'municipio' => 'Cruz Salmerón Acosta'],
        '244' => ['estado' => 'Sucre', 'municipio' => 'Libertador'],
        '245' => ['estado' => 'Sucre', 'municipio' => 'Mariño'],
        '246' => ['estado' => 'Sucre', 'municipio' => 'Mejía'],
        '247' => ['estado' => 'Sucre', 'municipio' => 'Montes'],
        '248' => ['estado' => 'Sucre', 'municipio' => 'Ribero'],
        '249' => ['estado' => 'Sucre', 'municipio' => 'Sucre'],
        '250' => ['estado' => 'Sucre', 'municipio' => 'Valdez'],

        // Táchira
        '251' => ['estado' => 'Táchira', 'municipio' => 'Andrés Bello'],
        '252' => ['estado' => 'Táchira', 'municipio' => 'Antonio Rómulo Costa'],
        '253' => ['estado' => 'Táchira', 'municipio' => 'Ayacucho'],
        '254' => ['estado' => 'Táchira', 'municipio' => 'Bolívar'],
        '255' => ['estado' => 'Táchira', 'municipio' => 'Cárdenas'],
        '256' => ['estado' => 'Táchira', 'municipio' => 'Córdoba'],
        '257' => ['estado' => 'Táchira', 'municipio' => 'Fernández Feo'],
        '258' => ['estado' => 'Táchira', 'municipio' => 'Francisco De Miranda'],
        '259' => ['estado' => 'Táchira', 'municipio' => 'Garcia De Hevia'],
        '260' => ['estado' => 'Táchira', 'municipio' => 'Guasimos'],
        '261' => ['estado' => 'Táchira', 'municipio' => 'Independencia'],
        '262' => ['estado' => 'Táchira', 'municipio' => 'Jauregui'],
        '263' => ['estado' => 'Táchira', 'municipio' => 'José María Vargas'],
        '264' => ['estado' => 'Táchira', 'municipio' => 'Junín'],
        '265' => ['estado' => 'Táchira', 'municipio' => 'Libertad'],
        '266' => ['estado' => 'Táchira', 'municipio' => 'Libertador'],
        '267' => ['estado' => 'Táchira', 'municipio' => 'Lobatera'],
        '268' => ['estado' => 'Táchira', 'municipio' => 'Michelena'],
        '269' => ['estado' => 'Táchira', 'municipio' => 'Panamericano'],
        '270' => ['estado' => 'Táchira', 'municipio' => 'Pedro María Ureña'],
        '271' => ['estado' => 'Táchira', 'municipio' => 'Rafael Urdaneta'],
        '272' => ['estado' => 'Táchira', 'municipio' => 'Samuel Darío Maldonado'],
        '273' => ['estado' => 'Táchira', 'municipio' => 'San Cristóbal'],
        '274' => ['estado' => 'Táchira', 'municipio' => 'Seboruco'],
        '275' => ['estado' => 'Táchira', 'municipio' => 'Simón Rodríguez'],
        '276' => ['estado' => 'Táchira', 'municipio' => 'Sucre'],
        '277' => ['estado' => 'Táchira', 'municipio' => 'Torbes'],
        '278' => ['estado' => 'Táchira', 'municipio' => 'Uribante'],
        '279' => ['estado' => 'Táchira', 'municipio' => 'San Judas Tadeo'],

        // Trujillo
        '280' => ['estado' => 'Trujillo', 'municipio' => 'Andrés Bello'],
        '281' => ['estado' => 'Trujillo', 'municipio' => 'Boconó'],
        '282' => ['estado' => 'Trujillo', 'municipio' => 'Bolívar'],
        '283' => ['estado' => 'Trujillo', 'municipio' => 'Candelaria'],
        '284' => ['estado' => 'Trujillo', 'municipio' => 'Carache'],
        '285' => ['estado' => 'Trujillo', 'municipio' => 'Escuque'],
        '286' => ['estado' => 'Trujillo', 'municipio' => 'José Felipe Márquez Cañizales'],
        '287' => ['estado' => 'Trujillo', 'municipio' => 'Juan Vicente Campo Elías'],
        '288' => ['estado' => 'Trujillo', 'municipio' => 'La Ceiba'],
        '289' => ['estado' => 'Trujillo', 'municipio' => 'Miranda'],
        '290' => ['estado' => 'Trujillo', 'municipio' => 'Monte Carmelo'],
        '291' => ['estado' => 'Trujillo', 'municipio' => 'Motatán'],
        '292' => ['estado' => 'Trujillo', 'municipio' => 'Pampán'],
        '293' => ['estado' => 'Trujillo', 'municipio' => 'Pampanito'],
        '294' => ['estado' => 'Trujillo', 'municipio' => 'Rafael Rangel'],
        '295' => ['estado' => 'Trujillo', 'municipio' => 'San Rafael De Carvajal'],
        '296' => ['estado' => 'Trujillo', 'municipio' => 'Sucre'],
        '297' => ['estado' => 'Trujillo', 'municipio' => 'Trujillo'],
        '298' => ['estado' => 'Trujillo', 'municipio' => 'Urdaneta'],
        '299' => ['estado' => 'Trujillo', 'municipio' => 'Valera'],

        // Yaracuy
        '300' => ['estado' => 'Yaracuy', 'municipio' => 'Arístides Bastidas'],
        '301' => ['estado' => 'Yaracuy', 'municipio' => 'Bolívar'],
        '302' => ['estado' => 'Yaracuy', 'municipio' => 'Bruzual'],
        '303' => ['estado' => 'Yaracuy', 'municipio' => 'Cocorote'],
        '304' => ['estado' => 'Yaracuy', 'municipio' => 'Independencia'],
        '305' => ['estado' => 'Yaracuy', 'municipio' => 'José Antonio Páez'],
        '306' => ['estado' => 'Yaracuy', 'municipio' => 'La Trinidad'],
        '307' => ['estado' => 'Yaracuy', 'municipio' => 'Manuel Monge'],
        '308' => ['estado' => 'Yaracuy', 'municipio' => 'Nirgua'],
        '309' => ['estado' => 'Yaracuy', 'municipio' => 'Peña'],
        '310' => ['estado' => 'Yaracuy', 'municipio' => 'San Felipe'],
        '311' => ['estado' => 'Yaracuy', 'municipio' => 'Sucre'],
        '312' => ['estado' => 'Yaracuy', 'municipio' => 'Urachiche'],
        '313' => ['estado' => 'Yaracuy', 'municipio' => 'Veroes'],

        // Zulia
        '314' => ['estado' => 'Zulia', 'municipio' => 'Almirante Padilla'],
        '315' => ['estado' => 'Zulia', 'municipio' => 'Baralt'],
        '316' => ['estado' => 'Zulia', 'municipio' => 'Cabimas'],
        '317' => ['estado' => 'Zulia', 'municipio' => 'Catatumbo'],
        '318' => ['estado' => 'Zulia', 'municipio' => 'Colón'],
        '319' => ['estado' => 'Zulia', 'municipio' => 'Francisco Javier Pulgar'],
        '320' => ['estado' => 'Zulia', 'municipio' => 'Jesús Enrique Lossada'],
        '321' => ['estado' => 'Zulia', 'municipio' => 'Jesús María Semprún'],
        '322' => ['estado' => 'Zulia', 'municipio' => 'La Cañada De Urdaneta'],
        '323' => ['estado' => 'Zulia', 'municipio' => 'Lagunillas'],
        '324' => ['estado' => 'Zulia', 'municipio' => 'Machiques De Perijá'],
        '325' => ['estado' => 'Zulia', 'municipio' => 'Mara'],
        '326' => ['estado' => 'Zulia', 'municipio' => 'Maracaibo'],
        '327' => ['estado' => 'Zulia', 'municipio' => 'Miranda'],
        '328' => ['estado' => 'Zulia', 'municipio' => 'Páez'],
        '329' => ['estado' => 'Zulia', 'municipio' => 'Rosario De Perijá'],
        '330' => ['estado' => 'Zulia', 'municipio' => 'San Francisco'],
        '331' => ['estado' => 'Zulia', 'municipio' => 'Santa Rita'],
        '332' => ['estado' => 'Zulia', 'municipio' => 'Simón Bolívar'],
        '333' => ['estado' => 'Zulia', 'municipio' => 'Sucre'],
        '334' => ['estado' => 'Zulia', 'municipio' => 'Valmore Rodríguez'],

        // La Guaira
        '335' => ['estado' => 'La Guaira', 'municipio' => 'La Guaira'],
    ];

    /**
     * Build reverse lookup: estado_name + municipio_name => location_code
     */
    private static $locationLookup = null;

    /**
     * Build the reverse lookup map for quick access
     */
    private static function buildLocationLookup()
    {
        if (self::$locationLookup === null) {
            self::$locationLookup = [];
            foreach (self::$locationMap as $code => $location) {
                $key = strtolower($location['estado'] . '|' . $location['municipio']);
                self::$locationLookup[$key] = $code;
            }
        }
        return self::$locationLookup;
    }

    /**
     * State name to SUDEASEG code mapping (for fallback)
     */
    private static $stateNameToCode = [
        'Distrito Capital' => '01',
        'Amazonas' => '02',
        'Anzoátegui' => '03',
        'Apure' => '04',
        'Aragua' => '05',
        'Barinas' => '06',
        'Bolívar' => '07',
        'Carabobo' => '08',
        'Cojedes' => '09',
        'Delta Amacuro' => '10',
        'Falcón' => '11',
        'Guárico' => '12',
        'Lara' => '13',
        'Mérida' => '14',
        'Miranda' => '15',
        'Monagas' => '16',
        'Nueva Esparta' => '17',
        'Portuguesa' => '18',
        'Sucre' => '19',
        'Táchira' => '20',
        'Trujillo' => '21',
        'Vargas' => '22',
        'Yaracuy' => '23',
        'Zulia' => '24',
        'La Guaira' => '25',
    ];

    public function rules()
    {
        return [
            [['date_range_type'], 'string'],
            [['date_from', 'date_to'], 'date', 'format' => 'php:Y-m-d'],
            [['date_from', 'date_to'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'date_range_type' => 'Rango de Fechas',
            'date_from' => 'Fecha Desde',
            'date_to' => 'Fecha Hasta',
        ];
    }

    public static function getDateRangeOptions()
    {
        return [
            self::DATE_RANGE_CUSTOM => 'Personalizado',
            self::DATE_RANGE_TODAY => 'Hoy',
            self::DATE_RANGE_YESTERDAY => 'Ayer',
            self::DATE_RANGE_THIS_WEEK => 'Esta Semana',
            self::DATE_RANGE_LAST_WEEK => 'Semana Pasada',
            self::DATE_RANGE_THIS_MONTH => 'Este Mes',
            self::DATE_RANGE_LAST_MONTH => 'Mes Pasado',
            self::DATE_RANGE_LAST_30_DAYS => 'Últimos 30 Días',
            self::DATE_RANGE_LAST_90_DAYS => 'Últimos 90 Días',
            self::DATE_RANGE_THIS_YEAR => 'Este Año',
            self::DATE_RANGE_LAST_YEAR => 'Año Pasado',
            self::DATE_RANGE_SINCE_INCIDENTS => 'Desde 24/06/2026 hasta hoy',
        ];
    }

    /**
     * Get the official SUDEASEG location code (single code from Table 2)
     */
    public static function getLocationCode($stateName, $municipioName)
    {
        if (empty($stateName) || empty($municipioName)) {
            return '';
        }

        $stateName = trim($stateName);
        $municipioName = trim($municipioName);

        $lookup = self::buildLocationLookup();
        $key = strtolower($stateName . '|' . $municipioName);

        if (isset($lookup[$key])) {
            return $lookup[$key];
        }

        foreach (self::$locationMap as $code => $location) {
            $stateMatch = strpos(strtolower($location['estado']), strtolower($stateName)) !== false ||
                strpos(strtolower($stateName), strtolower($location['estado'])) !== false;
            $muniMatch = strpos(strtolower($location['municipio']), strtolower($municipioName)) !== false ||
                strpos(strtolower($municipioName), strtolower($location['municipio'])) !== false;

            if ($stateMatch && $muniMatch) {
                return $code;
            }
        }

        foreach (self::$locationMap as $code => $location) {
            if (
                strpos(strtolower($location['estado']), strtolower($stateName)) !== false ||
                strpos(strtolower($stateName), strtolower($location['estado'])) !== false
            ) {
                return $code;
            }
        }

        return '';
    }

    /**
     * Get state code from state name (for fallback)
     */
    public static function getStateCode($stateName)
    {
        if (isset(self::$stateNameToCode[$stateName])) {
            return self::$stateNameToCode[$stateName];
        }

        $stateNameLower = strtolower(trim($stateName));
        foreach (self::$stateNameToCode as $name => $code) {
            if (strtolower($name) === $stateNameLower) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Get location code from the clinic (where the medical event occurred)
     */
    private function getLocationFromClinic($claim)
    {
        $estado = $claim['clinica_estado'] ?? null;
        $municipio = $claim['clinica_municipio'] ?? null;
        $ciudad = $claim['clinica_ciudad'] ?? null;

        if (!empty($estado) && is_numeric($estado)) {
            $estado = $this->getEstadoNameByCode($estado);
        }

        if (!empty($municipio) && is_numeric($municipio)) {
            $municipio = $this->getMunicipioNameByCode($municipio);
        }

        if (!empty($ciudad) && is_numeric($ciudad)) {
            $ciudad = $this->getCiudadNameByCode($ciudad);
        }

        if (!empty($estado) && !empty($municipio)) {
            $locationCode = self::getLocationCode($estado, $municipio);
            if (!empty($locationCode)) {
                return $locationCode;
            }
        }

        if (!empty($estado) && !empty($ciudad)) {
            $locationCode = self::getLocationCode($estado, $ciudad);
            if (!empty($locationCode)) {
                return $locationCode;
            }
        }

        if (!empty($estado)) {
            foreach (self::$locationMap as $code => $location) {
                if (
                    strpos(strtolower($location['estado']), strtolower($estado)) !== false ||
                    strpos(strtolower($estado), strtolower($location['estado'])) !== false
                ) {
                    return $code;
                }
            }
        }

        return '';
    }

    /**
     * Get estado name from numeric code
     */
    private function getEstadoNameByCode($code)
    {
        if (empty($code)) {
            return null;
        }

        $estado = RmEstado::find()
            ->where(['codigo' => (int)$code])
            ->one();

        return $estado ? $estado->nombre : null;
    }

    /**
     * Get municipio name from numeric code
     */
    private function getMunicipioNameByCode($code)
    {
        if (empty($code)) {
            return null;
        }

        $municipio = RmMunicipio::find()
            ->where(['codigo_muni' => (int)$code])
            ->one();

        return $municipio ? $municipio->nombre : null;
    }

    /**
     * Get ciudad name from numeric code
     */
    private function getCiudadNameByCode($code)
    {
        if (empty($code)) {
            return null;
        }

        $ciudad = RmCiudad::find()
            ->where(['codigo_ciudad' => (int)$code])
            ->one();

        return $ciudad ? $ciudad->nombre : null;
    }

    /**
     * Get the exchange rate for a specific date
     */
    private function getTipoCambio($date)
    {
        if (empty($date)) {
            return '0,00';
        }

        $tasa = TasaCambio::find()
            ->where(['fecha' => $date])
            ->andWhere(['IS', 'deleted_at', null])
            ->orderBy(['hora' => SORT_DESC])
            ->one();

        if ($tasa) {
            return number_format((float)$tasa->tasa_cambio, 2, ',', '');
        }

        $tasa = TasaCambio::find()
            ->where(['<=', 'fecha', $date])
            ->andWhere(['IS', 'deleted_at', null])
            ->orderBy(['fecha' => SORT_DESC, 'hora' => SORT_DESC])
            ->one();

        if ($tasa) {
            return number_format((float)$tasa->tasa_cambio, 2, ',', '');
        }

        return '0,00';
    }

    /**
     * Map currency to SUDEASEG code (Table 3)
     */
    private function mapMonedaToPsnp($monedaValor)
    {
        if (empty($monedaValor)) {
            return '02';
        }

        $monedaStr = strtoupper(trim((string)$monedaValor));

        $textMap = [
            'BS' => '01',
            'BS.' => '01',
            'BOLIVARES' => '01',
            'BOLIVAR' => '01',
            'VES' => '01',
            '1' => '01',
            'USD' => '02',
            'DOLARES' => '02',
            'DOLAR' => '02',
            '$' => '02',
            '2' => '02',
            'EUR' => '03',
            'EUROS' => '03',
            'EURO' => '03',
            '3' => '03',
        ];

        if (isset($textMap[$monedaStr])) {
            return $textMap[$monedaStr];
        }

        if (in_array($monedaStr, ['00', '01', '02', '03'])) {
            return $monedaStr;
        }

        return '02';
    }

    /**
     * Map claim status to SUDEASEG code (Table 7)
     * All records from SisSiniestro are "Notificado" (01)
     */
    private function mapStatusToPsnp($status)
    {
        return '01';
    }

    /**
     * Determine the cause of event for PSNP report
     */
    private function getCauseOfEvent($claim)
    {
        // Step 1: Check if description contains accident keywords
        $hasAccidentKeyword = false;
        if (!empty($claim['descripcion'])) {
            $desc = strtolower($claim['descripcion']);
            $accidentKeywords = ['accidente', 'lesión', 'trauma', 'caída', 'golpe', 'fractura', 'quemadura', 'herida'];

            foreach ($accidentKeywords as $keyword) {
                if (strpos($desc, $keyword) !== false) {
                    $hasAccidentKeyword = true;
                    break;
                }
            }
        }

        // Step 2: If no accident keyword, return "Otras causas"
        if (!$hasAccidentKeyword) {
            return 'Otras causas';
        }

        // Step 3: If accident keyword is found, check the date
        // Only events ON or AFTER the earthquake date (2026-06-24) are "Lesión por accidente"
        if (!empty($claim['fecha']) && $claim['fecha'] >= '2026-06-24') {
            return 'Lesión por accidente';
        }

        // Step 4: Events BEFORE the earthquake date with accident keywords → "Otras causas"
        return 'Otras causas';
    }

    /**
     * Generate a unique event number
     */
    private function generateEventNumber($claimId, $prefix = 'EVT')
    {
        return $prefix . '-' . date('Y') . '-' . str_pad($claimId, 6, '0', STR_PAD_LEFT);
    }

    private function formatDate($dateStr)
    {
        if (empty($dateStr) || $dateStr === '0000-00-00') {
            return '';
        }
        $ts = strtotime($dateStr);
        if ($ts === false) {
            return $dateStr;
        }
        return date('d/m/Y', $ts);
    }

    private function formatDateTime($dateStr, $timeStr = null)
    {
        if (empty($dateStr)) {
            return '';
        }
        $dateFormatted = $this->formatDate($dateStr);
        if (!empty($timeStr)) {
            $timeStr = substr($timeStr, 0, 5);
            return $dateFormatted . ' ' . $timeStr;
        }
        return $dateFormatted;
    }

    private function formatAmount($amount)
    {
        return number_format((float)($amount ?? 0), 2, ',', '');
    }

    public function applyDateRange($query)
    {
        $today = date('Y-m-d');

        switch ($this->date_range_type) {
            case self::DATE_RANGE_TODAY:
                $this->date_from = $today;
                $this->date_to = $today;
                break;
            case self::DATE_RANGE_YESTERDAY:
                $this->date_from = date('Y-m-d', strtotime('-1 day'));
                $this->date_to = $this->date_from;
                break;
            case self::DATE_RANGE_THIS_WEEK:
                $this->date_from = date('Y-m-d', strtotime('monday this week'));
                $this->date_to = $today;
                break;
            case self::DATE_RANGE_LAST_WEEK:
                $this->date_from = date('Y-m-d', strtotime('monday last week'));
                $this->date_to = date('Y-m-d', strtotime('sunday last week'));
                break;
            case self::DATE_RANGE_THIS_MONTH:
                $this->date_from = date('Y-m-01');
                $this->date_to = $today;
                break;
            case self::DATE_RANGE_LAST_MONTH:
                $this->date_from = date('Y-m-01', strtotime('first day of previous month'));
                $this->date_to = date('Y-m-t', strtotime('last day of previous month'));
                break;
            case self::DATE_RANGE_LAST_30_DAYS:
                $this->date_from = date('Y-m-d', strtotime('-30 days'));
                $this->date_to = $today;
                break;
            case self::DATE_RANGE_LAST_90_DAYS:
                $this->date_from = date('Y-m-d', strtotime('-90 days'));
                $this->date_to = $today;
                break;
            case self::DATE_RANGE_THIS_YEAR:
                $this->date_from = date('Y-01-01');
                $this->date_to = $today;
                break;
            case self::DATE_RANGE_LAST_YEAR:
                $this->date_from = date('Y-01-01', strtotime('-1 year'));
                $this->date_to = date('Y-12-31', strtotime('-1 year'));
                break;
            case self::DATE_RANGE_SINCE_INCIDENTS:
                $this->date_from = '2026-06-24';
                $this->date_to = $today;
                break;
            case self::DATE_RANGE_CUSTOM:
            default:
                break;
        }

        if ($this->date_from && $this->date_to) {
            $query->andWhere(['between', 's.fecha', $this->date_from, $this->date_to]);
        } elseif ($this->date_from) {
            $query->andWhere(['>=', 's.fecha', $this->date_from]);
        } elseif ($this->date_to) {
            $query->andWhere(['<=', 's.fecha', $this->date_to]);
        }
    }

    public function getPsnpData($params)
    {
        $this->load($params);

        $query = SisSiniestro::find()
            ->alias('s')
            ->select([
                's.id as claim_id',
                's.fecha',
                's.hora',
                's.fecha_atencion',
                's.hora_atencion',
                's.descripcion',
                's.atendido',
                's.costo_total',
                's.created_at as notification_date',
                's.appointment_status',
                's.nombre_doctor',
                's.idclinica',

                'ud.id as user_datos_id',
                'ud.nombres',
                'ud.apellidos',
                'ud.tipo_cedula',
                'ud.cedula',
                'ud.telefono',
                'ud.direccion',
                'ud.clinica_id',
                'ud.plan_id',
                'ud.moneda',

                'c.id as contrato_id',
                'c.nrocontrato',
                'c.fecha_ini',
                'c.fecha_ven',
                'c.created_at as contrato_created_at',

                'pl.cobertura as plan_cobertura',
                'pl.nombre as plan_nombre',

                'clin.id as clinica_id',
                'clin.nombre as clinica_nombre',
                'clin.codigo_clinica as clinica_codigo',
                'clin.estado as clinica_estado',
                'clin.ciudad as clinica_ciudad',
                'clin.municipio as clinica_municipio',
                'clin.parroquia as clinica_parroquia',
            ])
            ->innerJoin(['ud' => 'user_datos'], 'ud.id = s.iduser')
            ->leftJoin(['c' => 'contratos'], 'c.user_id = ud.id AND c.estatus != :anulado', [':anulado' => 'Anulado'])
            ->leftJoin(['pl' => 'planes'], 'pl.id = COALESCE(ud.plan_id, c.plan_id)')
            ->innerJoin(['clin' => 'rm_clinica'], 'clin.id = s.idclinica')
            ->where(['s.es_cita' => 0])
            ->andWhere(['IS', 's.deleted_at', null])
            ->orderBy(['s.fecha' => SORT_DESC, 's.hora' => SORT_DESC])
            ->asArray();

        $query->andWhere(['>=', 's.fecha', '2026-06-24']);

        if (UserHelper::hasClinicAccess()) {
            $clinicId = UserHelper::getMyClinicaId();
            if ($clinicId) {
                $query->andWhere(['s.idclinica' => $clinicId]);
            }
        }

        $this->applyDateRange($query);

        $claims = $query->all();
        $psnpRows = [];
        $counter = 1;

        foreach ($claims as $claim) {
            $contractorId = ($claim['tipo_cedula'] ?: 'V') . '-' . $claim['cedula'];
            $contractorName = trim(($claim['nombres'] ?? '') . ' ' . ($claim['apellidos'] ?? ''));

            $locationCode = $this->getLocationFromClinic($claim);
            $eventNumber = $this->generateEventNumber($claim['claim_id'], 'CLM');
            $cause = $this->getCauseOfEvent($claim);
            $statusCode = '01';
            $monedaPsnp = $this->mapMonedaToPsnp($claim['moneda'] ?? '02');
            $tipoCambio = $this->getTipoCambio($claim['fecha'] ?? date('Y-m-d'));
            $montoCobertura = $claim['plan_cobertura'] ?? 0;
            $valorEstimado = $claim['costo_total'] ?? 0;
            $fechaNotificacion = $claim['notification_date'] ?? $claim['created_at'] ?? date('Y-m-d');
            $fechaOcurrencia = $claim['fecha'] ?? '';
            $horaOcurrencia = $claim['hora'] ?? '';

            $psnpRows[] = [
                'n_inscripcion_sudeaseg' => 'MP-000013',
                'n_poliza' => $claim['nrocontrato'] ?? '',
                'fecha_ini_vigencia' => $this->formatDate($claim['fecha_ini'] ?? ''),
                'fecha_fin_vigencia' => $this->formatDate($claim['fecha_ven'] ?? ''),
                'n_evento_salud' => $eventNumber,
                'nombre_afiliado' => $contractorName,
                'cedula_afiliado' => $contractorId,
                'ramo' => '01',
                'nombre_tomador' => $contractorName,
                'cedula_tomador' => $contractorId,
                'ubicacion_geografica' => $locationCode,
                'suma_asegurada' => $this->formatAmount($montoCobertura),
                'causa_evento' => $cause,
                'fecha_hora_ocurrencia' => $this->formatDateTime($fechaOcurrencia, $horaOcurrencia),
                'fecha_notificacion' => $this->formatDate($fechaNotificacion),
                'valor_estimado_evento' => $this->formatAmount($valorEstimado),
                'estatus_evento' => $statusCode,
                'moneda_original' => $monedaPsnp,
                'tipo_cambio' => $tipoCambio,
                'codigo_ccr' => '0',
            ];

            $counter++;
        }

        return $psnpRows;
    }

    public function getSummary($params)
    {
        $data = $this->getPsnpData($params);
        return [
            'total_rows' => count($data),
            'total_claims' => count($data),
        ];
    }

    /**
     * Get all locations with their codes (for dropdowns)
     */
    public static function getLocationList()
    {
        $list = [];
        foreach (self::$locationMap as $code => $location) {
            $list[$code] = $location['estado'] . ' - ' . $location['municipio'];
        }
        asort($list);
        return $list;
    }
}
