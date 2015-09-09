<?php

/* WhiteBoardBundle:Whiteboard:layout.html.twig */
class __TwigTemplate_43f2506369f750e1988908440b32bf6fd30032a533d5b23fdd8ead3d6cb4f331 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'stylesheets' => array($this, 'block_stylesheets'),
            'container' => array($this, 'block_container'),
            'javascripts' => array($this, 'block_javascripts'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_8ee1708ce8e3f346164185817f82545508595f6c016d39c6bb83cf54e676fc59 = $this->env->getExtension("native_profiler");
        $__internal_8ee1708ce8e3f346164185817f82545508595f6c016d39c6bb83cf54e676fc59->enter($__internal_8ee1708ce8e3f346164185817f82545508595f6c016d39c6bb83cf54e676fc59_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WhiteBoardBundle:Whiteboard:layout.html.twig"));

        // line 1
        echo "<!DOCTYPE html>
<html>
<head>
  <meta charset=\"utf-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">

  <title>";
        // line 7
        $this->displayBlock('title', $context, $blocks);
        echo "</title>

  ";
        // line 9
        $this->displayBlock('stylesheets', $context, $blocks);
        // line 13
        echo "</head>

<body>
  <div class=\"container\">
  ";
        // line 17
        $this->displayBlock('container', $context, $blocks);
        // line 18
        echo "  </div>

  ";
        // line 20
        $this->displayBlock('javascripts', $context, $blocks);
        // line 25
        echo "
</body>
</html>";
        
        $__internal_8ee1708ce8e3f346164185817f82545508595f6c016d39c6bb83cf54e676fc59->leave($__internal_8ee1708ce8e3f346164185817f82545508595f6c016d39c6bb83cf54e676fc59_prof);

    }

    // line 7
    public function block_title($context, array $blocks = array())
    {
        $__internal_04e68a64cf638bf6e9dbb58b201a7fc2ce6ccfbe65c9c5463f62bc4ebc5074c0 = $this->env->getExtension("native_profiler");
        $__internal_04e68a64cf638bf6e9dbb58b201a7fc2ce6ccfbe65c9c5463f62bc4ebc5074c0->enter($__internal_04e68a64cf638bf6e9dbb58b201a7fc2ce6ccfbe65c9c5463f62bc4ebc5074c0_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        echo "Mon super site";
        
        $__internal_04e68a64cf638bf6e9dbb58b201a7fc2ce6ccfbe65c9c5463f62bc4ebc5074c0->leave($__internal_04e68a64cf638bf6e9dbb58b201a7fc2ce6ccfbe65c9c5463f62bc4ebc5074c0_prof);

    }

    // line 9
    public function block_stylesheets($context, array $blocks = array())
    {
        $__internal_8c6d8f37b389986e26ef5bd0e3f8061a94bf910330bca1a192c2bda42f953ae9 = $this->env->getExtension("native_profiler");
        $__internal_8c6d8f37b389986e26ef5bd0e3f8061a94bf910330bca1a192c2bda42f953ae9->enter($__internal_8c6d8f37b389986e26ef5bd0e3f8061a94bf910330bca1a192c2bda42f953ae9_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "stylesheets"));

        // line 10
        echo "    ";
        // line 11
        echo "    <link rel=\"stylesheet\" href=\"//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css\">
  ";
        
        $__internal_8c6d8f37b389986e26ef5bd0e3f8061a94bf910330bca1a192c2bda42f953ae9->leave($__internal_8c6d8f37b389986e26ef5bd0e3f8061a94bf910330bca1a192c2bda42f953ae9_prof);

    }

    // line 17
    public function block_container($context, array $blocks = array())
    {
        $__internal_a90030b462bc271aaebd0c0c798b6fd48ed40d2a29ab989d884f3cb5e4c42d53 = $this->env->getExtension("native_profiler");
        $__internal_a90030b462bc271aaebd0c0c798b6fd48ed40d2a29ab989d884f3cb5e4c42d53->enter($__internal_a90030b462bc271aaebd0c0c798b6fd48ed40d2a29ab989d884f3cb5e4c42d53_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "container"));

        echo "Hello world !";
        
        $__internal_a90030b462bc271aaebd0c0c798b6fd48ed40d2a29ab989d884f3cb5e4c42d53->leave($__internal_a90030b462bc271aaebd0c0c798b6fd48ed40d2a29ab989d884f3cb5e4c42d53_prof);

    }

    // line 20
    public function block_javascripts($context, array $blocks = array())
    {
        $__internal_4386d4a9a4221349c16cb8f9ef8f6c00e2c3bbbec87315c721917f2f9c0a90fd = $this->env->getExtension("native_profiler");
        $__internal_4386d4a9a4221349c16cb8f9ef8f6c00e2c3bbbec87315c721917f2f9c0a90fd->enter($__internal_4386d4a9a4221349c16cb8f9ef8f6c00e2c3bbbec87315c721917f2f9c0a90fd_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "javascripts"));

        // line 21
        echo "    ";
        // line 22
        echo "    <script src=\"//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js\"></script>
    <script src=\"//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js\"></script>
  ";
        
        $__internal_4386d4a9a4221349c16cb8f9ef8f6c00e2c3bbbec87315c721917f2f9c0a90fd->leave($__internal_4386d4a9a4221349c16cb8f9ef8f6c00e2c3bbbec87315c721917f2f9c0a90fd_prof);

    }

    public function getTemplateName()
    {
        return "WhiteBoardBundle:Whiteboard:layout.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  112 => 22,  110 => 21,  104 => 20,  92 => 17,  84 => 11,  82 => 10,  76 => 9,  64 => 7,  55 => 25,  53 => 20,  49 => 18,  47 => 17,  41 => 13,  39 => 9,  34 => 7,  26 => 1,);
    }
}
