<?php

/* @Twig/Exception/exception_full.html.twig */
class __TwigTemplate_02aad83ead1e1e5a1b0ae82dc7447e8ec008f0c8b05181250a4ea72d9c1b7e8a extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("@Twig/layout.html.twig", "@Twig/Exception/exception_full.html.twig", 1);
        $this->blocks = array(
            'head' => array($this, 'block_head'),
            'title' => array($this, 'block_title'),
            'body' => array($this, 'block_body'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "@Twig/layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_72b57b808a5bd051ee1afa8d590d5b7974f0534bac620ea0c69919fe429e91c4 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_72b57b808a5bd051ee1afa8d590d5b7974f0534bac620ea0c69919fe429e91c4->enter($__internal_72b57b808a5bd051ee1afa8d590d5b7974f0534bac620ea0c69919fe429e91c4_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Twig/Exception/exception_full.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_72b57b808a5bd051ee1afa8d590d5b7974f0534bac620ea0c69919fe429e91c4->leave($__internal_72b57b808a5bd051ee1afa8d590d5b7974f0534bac620ea0c69919fe429e91c4_prof);

    }

    // line 3
    public function block_head($context, array $blocks = array())
    {
        $__internal_4b7d840a66340bc79d8389a53103d3e50a19f5bd4c5cf211321a0840905e93ef = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_4b7d840a66340bc79d8389a53103d3e50a19f5bd4c5cf211321a0840905e93ef->enter($__internal_4b7d840a66340bc79d8389a53103d3e50a19f5bd4c5cf211321a0840905e93ef_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "head"));

        // line 4
        echo "    <link href=\"";
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\HttpFoundationExtension')->generateAbsoluteUrl($this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("bundles/framework/css/exception.css")), "html", null, true);
        echo "\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />
";
        
        $__internal_4b7d840a66340bc79d8389a53103d3e50a19f5bd4c5cf211321a0840905e93ef->leave($__internal_4b7d840a66340bc79d8389a53103d3e50a19f5bd4c5cf211321a0840905e93ef_prof);

    }

    // line 7
    public function block_title($context, array $blocks = array())
    {
        $__internal_1e5c810bd7db58bc3d20fba45a09ec6d0fc01fde83b4b8171cba5d2d073b8687 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_1e5c810bd7db58bc3d20fba45a09ec6d0fc01fde83b4b8171cba5d2d073b8687->enter($__internal_1e5c810bd7db58bc3d20fba45a09ec6d0fc01fde83b4b8171cba5d2d073b8687_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        // line 8
        echo "    ";
        echo twig_escape_filter($this->env, $this->getAttribute(($context["exception"] ?? $this->getContext($context, "exception")), "message", array()), "html", null, true);
        echo " (";
        echo twig_escape_filter($this->env, ($context["status_code"] ?? $this->getContext($context, "status_code")), "html", null, true);
        echo " ";
        echo twig_escape_filter($this->env, ($context["status_text"] ?? $this->getContext($context, "status_text")), "html", null, true);
        echo ")
";
        
        $__internal_1e5c810bd7db58bc3d20fba45a09ec6d0fc01fde83b4b8171cba5d2d073b8687->leave($__internal_1e5c810bd7db58bc3d20fba45a09ec6d0fc01fde83b4b8171cba5d2d073b8687_prof);

    }

    // line 11
    public function block_body($context, array $blocks = array())
    {
        $__internal_db406cd0abbd32ffeab84e5a530d653845640865420a47d1b34b33fad8e29e30 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_db406cd0abbd32ffeab84e5a530d653845640865420a47d1b34b33fad8e29e30->enter($__internal_db406cd0abbd32ffeab84e5a530d653845640865420a47d1b34b33fad8e29e30_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        // line 12
        echo "    ";
        $this->loadTemplate("@Twig/Exception/exception.html.twig", "@Twig/Exception/exception_full.html.twig", 12)->display($context);
        
        $__internal_db406cd0abbd32ffeab84e5a530d653845640865420a47d1b34b33fad8e29e30->leave($__internal_db406cd0abbd32ffeab84e5a530d653845640865420a47d1b34b33fad8e29e30_prof);

    }

    public function getTemplateName()
    {
        return "@Twig/Exception/exception_full.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  78 => 12,  72 => 11,  58 => 8,  52 => 7,  42 => 4,  36 => 3,  11 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("{% extends '@Twig/layout.html.twig' %}

{% block head %}
    <link href=\"{{ absolute_url(asset('bundles/framework/css/exception.css')) }}\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />
{% endblock %}

{% block title %}
    {{ exception.message }} ({{ status_code }} {{ status_text }})
{% endblock %}

{% block body %}
    {% include '@Twig/Exception/exception.html.twig' %}
{% endblock %}
", "@Twig/Exception/exception_full.html.twig", "C:\\Users\\Ares\\Desktop\\Grappbox\\API\\vendor\\symfony\\symfony\\src\\Symfony\\Bundle\\TwigBundle\\Resources\\views\\Exception\\exception_full.html.twig");
    }
}
