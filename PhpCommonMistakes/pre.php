<?php

class Config
{
    private $values = [];

    public function getValues() {
        return $this->values;
    }
}

Class Misconceptions
{
    public function __construct()
    {
        echo '<img src="Captain_Hindsight1.jpg" />';
    }

    public function example1()
    {
        echo '<h1> Foreach </h1>';
        $a = array('a', 'b', 'c', 'd');

        foreach ($a as &$v) { }
        foreach ($a as $v) { }

        var_dump($a);
    }

    public function example2()
    {
        echo '<h1> $_POST </h1>';
        // js
        $js = <<<JS
        $.ajax({
            url: 'http://my.site/some/path',
            method: 'post',
            data: JSON.stringify({a: 'a', b: 'b'}),
            contentType: 'application/json'
        });
JS;

        var_dump($_POST); // array(0) { }
        // application/x-www-form-urlencoded or multipart/form-data
        $this->request->getPost();
    }

    public function example3()
    {
        echo '<h1> References </h1>';
        $config = new Config();
        $config->getValues()['test'] = 'test';
        var_dump($config->getValues()['test']);
    }

    public function example4()
    {
        echo '<h1> isset vs empty vs is_null</h1>';
        $html = <<<html
<pre>
  Value of variable (var)	isset(var)	  empty(var)	   is_null(var)
  "" (an empty string)	        bool(true)	    bool(true)	    bool(false)
  " " (space)	                bool(true)	    bool(false)	    bool(false)
  FALSE	                        bool(true)	    bool(true)	    bool(false)
  TRUE	                        bool(true)	    bool(false)	    bool(false)
  array() (an empty array)	bool(true)	    bool(true)	    bool(false)
  NULL	                        bool(false)	    bool(true)	    bool(true)
  "0" (0 as a string)	        bool(true)	    bool(true)	    bool(false)
  0 (0 as an integer)	        bool(true)	    bool(true)	    bool(false)
  0.0 (0 as a float)	        bool(true)	    bool(true)	    bool(false)
  var var; 	                bool(false)	    bool(true)	    bool(true)
  NULL byte ("\ 0")	        bool(true)	    bool(false)	    bool(false)
</pre>
html;
        echo $html;
    }

    public function example5()
    {
        echo '<h1> Traits </h1>';

        echo "<h4>You should only use traits when multiple classes share the same functionality (likely dictated by the same interface). There's no sense in using a trait to provide functionality for a single class: that only obfuscates what the class does and a better design would move the trait's functionality into the relevant class.</h4>";

        echo '<h2>Traits with a single usage in skyrocket</h2>';
        echo 'app/Module/Frontend/Controller/MapTrait.php' . '<br>';
        echo 'app/Common/Form/AddressFormTrait.php' . '<br>';
        echo 'app/Common/Form/RendererAttributesTrait.php' . '  << used only by one trait!' . '<br>';
    }

    public function finish()
    {
        echo '<h1> Thank You! Q? </h1>';

        echo "<h4>I have performed my duties as <h1>Captain Hindsight</h1> it's time now to fly away</h4>";

        echo '<img src="Captain_Hindsight.jpg" />';
    }
}

(new Misconceptions())->example4();
//(new Misconceptions())->finish();
