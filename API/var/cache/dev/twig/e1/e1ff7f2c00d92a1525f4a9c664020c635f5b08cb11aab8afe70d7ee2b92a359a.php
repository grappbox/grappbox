<?php

/* @Twig/Exception/exception_full.html.twig */
class __TwigTemplate_9b6fb9a217943da183e0ae4b1af1360cb5460f3bc3bd025f8f6efaf076f70870 extends Twig_Template
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
        $__internal_a2bf82b964e620390aebd34f503e8122d4156d145c69caa6d04a4cd2128f4c07 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_a2bf82b964e620390aebd34f503e8122d4156d145c69caa6d04a4cd2128f4c07->enter($__internal_a2bf82b964e620390aebd34f503e8122d4156d145c69caa6d04a4cd2128f4c07_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Twig/Exception/exception_full.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_a2bf82b964e620390aebd34f503e8122d4156d145c69caa6d04a4cd2128f4c07->leave($__internal_a2bf82b964e620390aebd34f503e8122d4156d145c69caa6d04a4cd2128f4c07_prof);

    }

    // line 3
    public function block_head($context, array $blocks = array())
    {
        $__internal_b40cdb505e8088a939882fe100b427314b756b93071611517e08bed927922ef7 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_b40cdb505e8088a939882fe100b427314b756b93071611517e08bed927922ef7->enter($__internal_b40cdb505e8088a939882fe100b427314b756b93071611517e08bed927922ef7_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "head"));

        // line 4
        echo "    <link href=\"";
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\HttpFoundationExtension')->generateAbsoluteUrl($this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("bundles/framework/css/exception.css")), "html", null, true);
        echo "\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />
";
        
        $__internal_b40cdb505e8088a939882fe100b427314b756b93071611517e08bed927922ef7->leave($__internal_b40cdb505e8088a939882fe100b427314b756b93071611517e08bed927922ef7_prof);

    }

    // line 7
    public function block_title($context, array $blocks = array())
    {
        $__internal_d77a2049728a0d300bed3324108cbb195f44f4f6ea9e4d266f9e500714a47516 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_d77a2049728a0d300bed3324108cbb195f44f4f6ea9e4d266f9e500714a47516->enter($__internal_d77a2049728a0d300bed3324108cbb195f44f4f6ea9e4d266f9e500714a47516_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        // line 8
        echo "    ";
        echo twig_escape_filter($this->env, $this->getAttribute(($context["exception"] ?? $this->getContext($context, "exception")), "message", array()), "html", null, true);
        echo " (";
        echo twig_escape_filter($this->env, ($context["status_code"] ?? $this->getContext($context, "status_code")), "html", null, true);
        echo " ";
        echo twig_escape_filter($this->env, ($context["status_text"] ?? $this->getContext($context, "status_text")), "html", null, true);
        echo ")
";
        
        $__internal_d77a2049728a0d300bed3324108cbb195f44f4f6ea9e4d266f9e500714a47516->leave($__internal_d77a2049728a0d300bed3324108cbb195f44f4f6ea9e4d266f9e500714a47516_prof);

    }

    // line 11
    public function block_body($context, array $blocks = array())
    {
        $__internal_ec48eba83795ab34f080c75b844f85262b374bb50fe4a20d22b8f47eb8343794 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_ec48eba83795ab34f080c75b844f85262b374bb50fe4a20d22b8f47eb8343794->enter($__internal_ec48eba83795ab34f080c75b844f85262b374bb50fe4a20d22b8f47eb8343794_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        // line 12
        echo "    ";
        $this->loadTemplate("@Twig/Exception/exception.html.twig", "@Twig/Exception/exception_full.html.twig", 12)->display($context);
        
        $__internal_ec48eba83795ab34f080c75b844f85262b374bb50fe4a20d22b8f47eb8343794->leave($__internal_ec48eba83795ab34f080c75b844f85262b374bb50fe4a20d22b8f47eb8343794_prof);

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
