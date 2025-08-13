<?php
/***
 * Translate .po file with LLM
 *
 * @author Parsa Kafi (https://parsa.ws)
 * @version 1.0
 */

use Gettext\Generator\MoGenerator;
use Gettext\Generator\PoGenerator;
use Gettext\Loader\PoLoader;

set_time_limit( 0 );

$config = array(
	'po_file'                  => 'translate-file.po', // Main po file
	'mo_file'                  => 'translate-file.mo', // File name or set false
	'max_translate'            => 50, // Set -1 to unlimit
	'retranslate'              => false, // Do not retranslate translated words, true or false.
	'sleep_between_translates' => 1, // Time in seconds or set false
	'translate_from'           => array(
		'language' => 'English',
		'code'     => 'en'
	),
	'translate_to'             => array(
		'language' => 'Farsi/Persian',
		'code'     => 'fa-IR'
	),
	'api_url'                  => 'http://127.0.0.1:1234/v1/chat/completions'
);

$config['api_config'] = array(
	'model'       => 'mistralai/mistral-small-3.2',
	"messages"    => [
		[
			"role"    => "system",
			"content" => "You are a professional terminologist with fluent knowledge of " . $config['translate_from']['language'] . " and " . $config['translate_to']['language'] . ". You have extensive knowledge in Web, WordPress and WooCommerce plugin and are able to supplement the terms with correct translations into " . $config['translate_to']['language'] . ". In response just say translate without description and don`t repeat source text. Don`t use \"translate\" term in your response."
		],
		[
			"role"    => "user",
			"content" => "Translate the following text from " . $config['translate_from']['language'] . " to " . $config['translate_to']['language'] . ": "
		]
	],
	"temperature" => 0.7,
);

function translatePoFile( $config ) {
	require_once __DIR__ . '/vendor/autoload.php';

	if ( ! file_exists( $config['po_file'] ) ) {
		die( 'po file not found' );
	}

	$loader       = new PoLoader();
	$translations = $loader->loadFile( $config['po_file'] );
	$count        = $translations->count();
	$c            = 1;

	if ( $count === 0 ) {
		return;
	}

	echo 'Translating po file: ' . $config['po_file'] . "<br>";
	echo 'Line count: ' . $count . "<br>";

	echo "<br>";
	foreach ( $translations->getTranslations() as $translation ) {
		if ( ! $config['retranslate'] && $translation->isTranslated() ) {
			continue;
		}

		$translate = translateRequest( $translation->getId(), $config );
		if ( $translate ) {
			$translation->translate( $translate );
			echo $c . '- ' . $translation->getId() . ' >>>> ' . $translate . '<br>';
		}

		ob_flush(); // Flush output buffer to show progress immediately
		flush();

		if ( $config['sleep_between_translates'] && is_numeric( $config['sleep_between_translates'] ) ) {
			sleep( $config['sleep_between_translates'] );
		}

		$c ++;
		if ( $config['max_translate'] !== - 1 && $c > $config['max_translate'] ) {
			break;
		}
	}

	$generator = new PoGenerator();
	$generator->generateFile( $translations, $config['po_file'] );

	if ( $config['mo_file'] ) {
		$generator = new MoGenerator();
		$generator->generateFile( $translations, $config['mo_file'] );
	}
}

function translateRequest( $sentence, $config ) {
	$config['api_config']['messages'][1]['content'] .= $sentence;

	$ch = curl_init( $config['api_url'] );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $config['api_config'] ) );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, [
		"Content-Type: application/json"
	] );

	$response  = curl_exec( $ch );
	$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );

	if ( $http_code === 200 ) {
		$result = json_decode( $response, true );

		return $result['choices'][0]['message']['content'];
	}

	return false;
}

translatePoFile( $config );