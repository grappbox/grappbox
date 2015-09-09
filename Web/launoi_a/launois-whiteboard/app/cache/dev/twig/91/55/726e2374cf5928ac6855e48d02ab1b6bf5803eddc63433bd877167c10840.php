<?php

/* WhiteBoardBundle:Default:index.html.twig */
class __TwigTemplate_9155726e2374cf5928ac6855e48d02ab1b6bf5803eddc63433bd877167c10840 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_065d980d14b1fcbe192f7169f60a75f42041c59da841b659c6899b4fcb800204 = $this->env->getExtension("native_profiler");
        $__internal_065d980d14b1fcbe192f7169f60a75f42041c59da841b659c6899b4fcb800204->enter($__internal_065d980d14b1fcbe192f7169f60a75f42041c59da841b659c6899b4fcb800204_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WhiteBoardBundle:Default:index.html.twig"));

        // line 1
        echo "Hello ";
        echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : $this->getContext($context, "name")), "html", null, true);
        echo "!
";
        
        $__internal_065d980d14b1fcbe192f7169f60a75f42041c59da841b659c6899b4fcb800204->leave($__internal_065d980d14b1fcbe192f7169f60a75f42041c59da841b659c6899b4fcb800204_prof);

    }

    public function getTemplateName()
    {
        return "WhiteBoardBundle:Default:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  22 => 1,);
    }
}
