<?php

/**
 * Alias de confort pour la fonction Translate::translate()
 *
 * @see Translate::translate
 */
function tr($word, $return = false, $params = array()) {
	return Translate::translate($word, $return, $params);
}

class Translate {
	public static $words_fr = array();
	public static $words_en = array();
	public static $propositions_en = array();

    public static $at_least_one_modification = false;

	function __construct() {
		self::init();
	}

	/**
	 * Cette fonction initialise la classe et crée les variables disposant du contenu
	 */
	static function init() {
        self::createTree();
		self::get_words_fr();
		self::get_words_en();
		self::get_propositions_en();
	}

    static function createTree() {
        if (!FileAndDir::dexists(ROOT.DS.'translation'.DS.'fr'.DS)) {
            FileAndDir::createPath(ROOT.DS.'translation'.DS.'fr'.DS);
        }
        if (!FileAndDir::dexists(ROOT.DS.'translation'.DS.'en'.DS)) {
            FileAndDir::createPath(ROOT.DS.'translation'.DS.'en'.DS);
        }
    }

    /**
     * Cette fonction sert à traduire le texte. Si le mot n'est pas traduit, on l'ajoute à la liste pour qu'il le soit plus tard.
     * @param string $txt Le texte à traduire
     * @param boolean $return Si false, on fait un echo du texte. Si true, on le retourne.
     * @param array $params Les paramètres de texte à ajouter
     * @return mixed Le texte traduit si $return == true, sinon true après echo, sinon false
     */
	static function translate($txt, $return = false, $params = array()) {

        if  ($return === null) {
            $return = false;
        }

		if (!self::$words_fr) { self::init(); }

		$txt = self::clean_word($txt);

		if (!$txt) { return ''; }

		if (!self::check($txt, self::$words_fr)) {
			self::$words_fr[] = array('source'=>$txt,'trans'=>$txt);
            self::$at_least_one_modification = true;
		}

		if (defined('P_LANG') && P_LANG == 'en') {
            $txt = self::search($txt, self::$words_en);
		}

		if ($return === false) {
			echo $txt;
			return null;
		} else {
			return $txt;
		}
	}

    /**
     * @param string $txt La chaîne à chercher
     * @param array $source Le tableau source
     * @return string
     */
    static function check($txt, $source) {
        $found = false;
        $result = array_filter($source, function($element) use ($txt) {
            return $element['source'] == $txt;
        });
        if (count($result)){
            $found = true;
        }
        return $found;
    }

    /**
     * @param string $txt La chaîne à chercher
     * @param array $source Le tableau source
     * @return string
     */
    static function search($txt, $source) {
        $result = array_filter($source, function($element) use ($txt) {
            return $element['source'] == $txt;
        });
        if (count($result)){
            sort($result);
            $txt = $result[0]['trans'];
        }
        return $txt;
    }


	/**
	 * Cette fonction récupère les mots français du site
	 * @return array Les mots en français
	 */
	static function get_words_fr() {
        $file = ROOT.DS.'translation'.DS.'fr'.DS.'words.txt';
        if (FileAndDir::fexists($file)) {
            $w = FileAndDir::get($file);
            $w = json_decode($w, true) ?: array();
            self::$words_fr = $w;
            unset($w);
        }
		return self::$words_fr;
	}

	/**
	 * Cette fonction récupère les traductions fr=>en
	 * @return array Clé = mot en français ; Valeur = mot traduit en anglais
	 */
	static function get_words_en() {
        $file = ROOT.DS.'translation'.DS.'en'.DS.'words.txt';
        if (FileAndDir::fexists($file)) {
            $w = FileAndDir::get($file);
            $w = json_decode($w, true) ?: array();
            self::$words_en = $w;
            unset($w);
        }
        return self::$words_en;
	}

	/**
	 * Cette fonction récupère les propositions de traductions fr=>en
	 * @return array Clé = mot en français ; Valeur = proposition de traduction
	 */
	static function get_propositions_en() {
        $file = ROOT.DS.'translation'.DS.'en'.DS.'propositions_en.txt';
        if (FileAndDir::fexists($file)) {
            $w = FileAndDir::get($file);
            $w = json_decode($w, true) ?: array();
            self::$propositions_en = $w;
            unset($w);
        }
        return self::$propositions_en;
	}
	/**
	 * Cette fonction sert à ajouter ou éditer un mot traduit
	 * @param string $word_source Le mot ou l'expression à traduire
	 * @param string $trans La traduction
     * @return boolean
	 */
	static function write_words_en($word_source, $trans) {
        $word_source = self::clean_word($word_source);
		$trans = self::clean_word($trans);

        $changed = false;

        foreach  (self::$words_en as $k => $word) {
            if ($word['source'] == $word_source && self::$words_en[$k]['trans'] != $trans) {
                self::$words_en[$k]['trans'] = $trans;
                $changed = true;
            }
        }

        if ($changed === false) {
            self::$words_en[] = array('source' => $word_source, 'trans' => $trans);
            $changed = true;
        }

        $text_to_write = json_encode(self::$words_en, 480);

		file_put_contents(ROOT.DS.'translation'.DS.'en'.DS.'words.txt', $text_to_write);
        return $changed;
	}

    /**
     * Cette fonction sert à "nettoyer" un mot ou une expression
     * @param string $word Le mot ou l'expression à traduire
     * @return string L'état du mot. 'saved' s'il a été inséré, ou false sinon
     */
	static function clean_word($word) {
// 		$word = Encoding::toISO8859($word);
// 		$word = Encoding::toUTF8($word);
// 		$word = preg_replace('#\n|\r#sUu', '', $word);
		$word = preg_replace('#\s\s+#sUu', ' ', $word);
		$word = str_replace('’', "'", $word);
		$word = str_replace('\\\'', "'", $word);
		$word = str_replace('★', '&#9733;', $word);
		$word = trim($word);
		return $word;
	}

	/**
	 * Cette fonction sert à ajouter une proposition de traduction
	 * @param string $word Le mot ou l'expression à traduire
	 * @param string $trans La traduction proposée
	 * @return mixed L'état de l'insertion
	 */
	static function write_propos_en($word, $trans) {
		$propositions_en = self::$propositions_en;
		if ($word && $trans && $word != $trans) {
			$word = self::clean_word($word);
			$trans = self::clean_word($trans);
			if (!isset($propositions_en[$word])) {
				$propositions_en[$word] = $trans;
			} else {
				while (isset($propositions_en[$word])) {
					$word .= ' ';
				}
				$propositions_en[$word] = $trans;
			}
			asort($propositions_en);
			ksort($propositions_en);

			$text_to_write = '';
			$_SESSION['words'][] = self::clean_word($word);
			foreach($propositions_en as $w => $t) {
				if ($text_to_write) { $text_to_write .= '*|*|*'; }
				$text_to_write .= $w.'=>'.$t;
			}
		}

	}

	/**
	 * Cette fonction sert à écrire les mots français dans la liste
	 * @return boolean Résultat de l'opération
	 */
	static function translate_writewords() {
		$words_for_translation = json_encode(self::$words_fr, 480);
//		$words_for_translation = implode("*|*|*", $words_for_translation);

		return file_put_contents(ROOT.DS.'translation'.DS.'fr'.DS.'words.txt', $words_for_translation);
	}
}