<?php

namespace App;

class DomxssCheck {
	protected $hasError = false;
	protected $hasSinkError = false;
	protected $sinkErrorMessage = null;
	protected $hasSourceError = false;
	protected $sourceErrorMessage = null;
	protected $response = null;

	public function __construct( $url ) {
		$this->response = new HTTPResponse( $url );
	}

	public function hasSources() {
		// RegEx from original authors
		// https://github.com/wisec/domxsswiki/wiki/Finding-DOMXSS
		$sourcePattern = "/(location\s*[\[.])|([.\[]\s*[\"']?\s*(arguments|dialogArguments|innerHTML|write(ln)?|open(Dialog)?|showModalDialog|cookie|URL|documentURI|baseURI|referrer|name|opener|parent|top|content|self|frames)\W)|(localStorage|sessionStorage|Database)/";

		$findings = preg_match( $sourcePattern, $this->response->body() );

		if ( $findings !== false && $findings > 0 ) {
			return true;
		}

		return false;
	}

	public function hasSinks() {
		// RegEx from original authors
		// https://github.com/wisec/domxsswiki/wiki/Finding-DOMXSS
		$sourcePattern = "/((src|href|data|location|code|value|action)\s*[\"'\]]*\s*\+?\s*=)|((replace|assign|navigate|getResponseHeader|open(Dialog)?|showModalDialog|eval|evaluate|execCommand|execScript|setTimeout|setInterval)\s*[\"'\]]*\s*\()/";

		$findings = preg_match( $sourcePattern, $this->response->body() );

		if ( $findings !== false && $findings > 0 ) {
			return true;
		}

		return false;
	}

	public function report() {

		if ( $this->response->hasErrors() ) {
			return [
				'name'         => 'DOMXSS',
				'hasError'     => true,
				'errorMessage' => [
					'placeholder' => 'NO_HTTP_RESPONSE',
					'values'      => []
				],
				'score'        => 0,
				'tests'        => []
			];
		}

		$score = 100;

		if ( ! $this->hasSinks() && ! $this->hasSources() ) {
			$score = 100;
		} else {
			if ( $this->hasSinks() ) {
				$score -= 50;
			}
			if ( $this->hasSources() ) {
				$score -= 50;
			}
		}


		return [
			'name'         => 'DOMXSS',
			'hasError'     => $this->hasError,
			'errorMessage' => null,
			'score'        => $score,
			'tests'        => [
				[
					'name'         => "HAS_SINKS",
					'hasError'     => $this->hasSinkError,
					'errorMessage' => $this->sinkErrorMessage,
					'score'        => $this->hasSinks() ? 0 : 100,
					'scoreType'    => 'info',
					'testDetails'  => [
						[
							'placeholder' => $this->hasSinks() ? 'SINKS_FOUND' : 'NO_SINKS_FOUND',
							'values'      => null
						]
					]
				],
				[
					'name'         => "HAS_SOURCES",
					'hasError'     => $this->hasSourceError,
					'errorMessage' => $this->sourceErrorMessage,
					'score'        => $this->hasSources() ? 0 : 100,
					'scoreType'    => 'info',
					'testDetails'  => [
						[
							'placeholder' => $this->hasSources() ? 'SOURCES_FOUND' : 'NO_SOURCES_FOUND',
							'values'      => null
						]
					]
				]
			]
		];
	}
}
