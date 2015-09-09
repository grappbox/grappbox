<?php

/* TwigBundle:Exception:exception_full.html.twig */
class __TwigTemplate_e532385342cdf4b6a6e5bc8b3b6162c2dc82388f74a8e28c2b30fd2db1b0e95e extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("TwigBundle::layout.html.twig", "TwigBundle:Exception:exception_full.html.twig", 1);
        $this->blocks = array(
            'head' => array($this, 'block_head'),
            'title' => array($this, 'block_title'),
            'body' => array($this, 'block_body'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "TwigBundle::layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_bb392df61e73a9540351b14cac90e3e034727672487b1719b406c1a88e60d929 = $this->env->getExtension("native_profiler");
        $__internal_bb392df61e73a9540351b14cac90e3e034727672487b1719b406c1a88e60d929->enter($__internal_bb392df61e73a9540351b14cac90e3e034727672487b1719b406c1a88e60d929_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:exception_full.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_bb392df61e73a9540351b14cac90e3e034727672487b1719b406c1a88e60d929->leave($__internal_bb392df61e73a9540351b14cac90e3e034727672487b1719b406c1a88e60d929_prof);

    }

    // line 3
    public function block_head($context, array $blocks = array())
    {
        $__internal_4d509f9ca552640000adb1ee2d34e8dd01933cc10c7173def5e3a747c35cdf9a = $this->env->getExtension("native_profiler");
        $__internal_4d509f9ca552640000adb1ee2d34e8dd01933cc10c7173def5e3a747c35cdf9a->enter($__internal_4d509f9ca552640000adb1ee2d34e8dd01933cc10c7173def5e3a747c35cdf9a_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "head"));

        // line 4
        echo "    <link href=\"";
        echo twig_escape_filter($this->env, $this->env->getExtension('request')->generateAbsoluteUrl($this->env->getExtension('asset')->getAssetUrl("bundles/framework/css/exception.css")), "html", null, true);
        echo "\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />
";
        
        $__internal_4d509f9ca552640000adb1ee2d34e8dd01933cc10c7173def5e3a747c35cdf9a->leave($__internal_4d509f9ca552640000adb1ee2d34e8dd01933cc10c7173def5e3a747c35cdf9a_prof);

    }

    // line 7
    public function block_title($context, array $blocks = array())
    {
        $__internal_68fa950192540f9dccbfdb6e89fcd16e5095457731e96a08ba28745b2a69d093 = $this->env->getExtension("native_profiler");
        $__internal_68fa950192540f9dccbfdb6e89fcd16e5095457731e96a08ba28745b2a69d093->enter($__internal_68fa950192540f9dccbfdb6e89fcd16e5095457731e96a08ba28745b2a69d093_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        // line 8
        echo "    ";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["exception"]) ? $context["exception"] : $this->getContext($context, "exception")), "message", array()), "html", null, true);
        echo " (";
        echo twig_escape_filter($this->env, (isset($context["status_code"]) ? $context["status_code"] : $this->getContext($context, "status_code")), "html", null, true);
        echo " ";
        echo twig_escape_filter($this->env, (isset($context["status_text"]) ? $context["status_text"] : $this->getContext($context, "status_text")), "html", null, true);
        echo ")
";
        
        $__internal_68fa950192540f9dccbfdb6e89fcd16e5095457731e96a08ba28745b2a69d093->leave($__internal_68fa950192540f9dccbfdb6e89fcd16e5095457731e96a08ba28745b2a69d093_prof);

    }

    // line 11
    public function block_body($context, array $blocks = array())
    {
        $__internal_05233cda56732851b887c834842cda1c560dd3ec61c6142f8c48c603e13827ee = $this->env->getExtension("native_profiler");
        $__internal_05233cda56732851b887c834842cda1c560dd3ec61c6142f8c48c603e13827ee->enter($__internal_05233cda56732851b887c834842cda1c560dd3ec61c6142f8c48c603e13827ee_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        // line 12
        echo "    ";
        $this->loadTemplate("TwigBundle:Exception:exception.html.twig", "TwigBundle:Exception:exception_full.html.twig", 12)->display($context);
        
        $__internal_05233cda56732851b887c834842cda1c560dd3ec61c6142f8c48c603e13827ee->leave($__internal_05233cda56732851b887c834842cda1c560dd3ec61c6142f8c48c603e13827ee_prof);

    }

    public function getTemplateName()
    {
        return "TwigBundle:Exception:exception_full.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  78 => 12,  72 => 11,  58 => 8,  52 => 7,  42 => 4,  36 => 3,  11 => 1,);
    }
}
