<?php

/* WhiteBoardBundle:Whiteboard:index.html.twig */
class __TwigTemplate_08663683ce4e7a6a29ae94952f829b141e1f03235c8d7f1e2ee1663e7f654b21 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("WhiteBoardBundle:Whiteboard:layout.html.twig", "WhiteBoardBundle:Whiteboard:index.html.twig", 1);
        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'container' => array($this, 'block_container'),
            'javascripts' => array($this, 'block_javascripts'),
            'stylesheets' => array($this, 'block_stylesheets'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "WhiteBoardBundle:Whiteboard:layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_ecbba2f8b9febbceec4904b04efc030fe6151da93f93d8fa90b8d6eb28c0ef9b = $this->env->getExtension("native_profiler");
        $__internal_ecbba2f8b9febbceec4904b04efc030fe6151da93f93d8fa90b8d6eb28c0ef9b->enter($__internal_ecbba2f8b9febbceec4904b04efc030fe6151da93f93d8fa90b8d6eb28c0ef9b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WhiteBoardBundle:Whiteboard:index.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_ecbba2f8b9febbceec4904b04efc030fe6151da93f93d8fa90b8d6eb28c0ef9b->leave($__internal_ecbba2f8b9febbceec4904b04efc030fe6151da93f93d8fa90b8d6eb28c0ef9b_prof);

    }

    // line 3
    public function block_title($context, array $blocks = array())
    {
        $__internal_10f59aebf192a068ec6618aa6cbd8347454d2da07ab30fcd129e2d1fd6d2a0b4 = $this->env->getExtension("native_profiler");
        $__internal_10f59aebf192a068ec6618aa6cbd8347454d2da07ab30fcd129e2d1fd6d2a0b4->enter($__internal_10f59aebf192a068ec6618aa6cbd8347454d2da07ab30fcd129e2d1fd6d2a0b4_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        echo "Mon super Whiteboard";
        
        $__internal_10f59aebf192a068ec6618aa6cbd8347454d2da07ab30fcd129e2d1fd6d2a0b4->leave($__internal_10f59aebf192a068ec6618aa6cbd8347454d2da07ab30fcd129e2d1fd6d2a0b4_prof);

    }

    // line 5
    public function block_container($context, array $blocks = array())
    {
        $__internal_8e5c021a96f0818f5cc22be8ac7314c4b97b9330a671c59a4ad80890fa20de10 = $this->env->getExtension("native_profiler");
        $__internal_8e5c021a96f0818f5cc22be8ac7314c4b97b9330a671c59a4ad80890fa20de10->enter($__internal_8e5c021a96f0818f5cc22be8ac7314c4b97b9330a671c59a4ad80890fa20de10_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "container"));

        // line 6
        echo "\t<div ng-app=\"whiteboardApp\" ng-controller=\"whiteboardCtrl\">
\t\t<div class=\"col-md-12\">
\t\t\t<h1>Mon Super WhiteBoard</h1>
\t\t</div>
\t\t<div class=\"col-md-12\">
\t\t\t<div id=\"menu\" class=\"col-md-2\">
\t\t\t\t<p class=\"col-md-12\">Actually in use : {[{tool}]}</p>
\t\t\t\t<button class=\"col-md-12 btn btn-default\" ng-click=\"selectTool('line')\">Line</button>
\t\t\t\t<button class=\"col-md-12 btn btn-default\" ng-click=\"selectTool('rect')\">Rectangle</button>
\t\t\t\t<button class=\"col-md-12 btn btn-default\" ng-click=\"selectTool('circle')\">Circle</button>
\t\t\t\t<button class=\"col-md-12 btn btn-default\" ng-click=\"selectAction('save')\">Save</button>
\t\t\t\t<button class=\"col-md-12 btn btn-default\" ng-click=\"selectAction('load')\">Load</button>
\t\t\t\t<button class=\"col-md-12 btn btn-default\" ng-click=\"selectAction('send')\">Send to server</button>
\t\t\t</div>
\t\t\t
\t\t\t<div id=\"board\" class=\"col-md-10\">
\t\t\t\t<div>
\t\t\t\t\t<canvas drawing></canvas>
\t\t\t\t</div>
\t\t\t</div>
\t\t</div>
\t</div>
";
        
        $__internal_8e5c021a96f0818f5cc22be8ac7314c4b97b9330a671c59a4ad80890fa20de10->leave($__internal_8e5c021a96f0818f5cc22be8ac7314c4b97b9330a671c59a4ad80890fa20de10_prof);

    }

    // line 30
    public function block_javascripts($context, array $blocks = array())
    {
        $__internal_8a826a0f2f0d78d168814d85d36162135eedbfa59fd469006e9bdb8efcde9423 = $this->env->getExtension("native_profiler");
        $__internal_8a826a0f2f0d78d168814d85d36162135eedbfa59fd469006e9bdb8efcde9423->enter($__internal_8a826a0f2f0d78d168814d85d36162135eedbfa59fd469006e9bdb8efcde9423_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "javascripts"));

        // line 31
        echo "\t";
        // line 32
        echo "\t<script src=\"//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js\"></script>
\t<script src=\"//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js\"></script>
\t<script src=\"http://ajax.googleapis.com/ajax/libs/angularjs/1.3.14/angular.min.js\"></script>
\t<script >
\t\tvar app = angular.module(\"whiteboardApp\", [])
\t\t\t\t\t.config(function(\$interpolateProvider){
\t\t\t\t\t\t\$interpolateProvider.startSymbol('{[{').endSymbol('}]}');
\t\t\t\t\t});

\t\tapp.directive(\"drawing\", function(){
\t\t  return {
\t\t    restrict: \"A\",
\t\t    link: function(\$scope, element){
\t\t    \tvar drawing = false;
\t\t  \t\tvar centerX;
\t\t  \t\tvar centerY;
\t\t\t\tvar currentX;
\t\t\t\tvar currentY;

\t\t\t\tvar ctx = element[0].getContext('2d');

\t\t\t\tvar realHeight = ctx.canvas.clientHeight;
\t\t\t\tvar realWidth = ctx.canvas.clientWidth;
\t\t\t\tctx.canvas.width = realWidth;
\t\t\t\tctx.canvas.height = realHeight;
\t\t      
\t\t\t\telement.bind('mousedown', function(event){
\t\t\t\t\tcenterX = event.offsetX;
\t\t\t\t\tcenterY = event.offsetY;

\t\t\t\t\tctx.beginPath();

\t\t\t\t\tdrawing = true;
\t\t\t\t});

\t\t\t\telement.bind('mousemove', function(event){
\t\t\t\t\tif(drawing){
\t\t\t\t\t\tcurrentX = event.offsetX;
\t\t\t\t\t\tcurrentY = event.offsetY;
\t\t\t\t\t\t//draw(centerX, centerY, currentX, currentY);
\t\t\t\t\t}
\t\t\t\t});

\t\t\t\telement.bind('mouseup', function(event){
\t\t\t\t\tif (\$scope.tool != 'none')
\t\t\t\t\t\tdraw(centerX, centerY, currentX, currentY);
\t\t\t\t\telse
\t\t\t\t\t\talert(\"You need to select a tool !\");
\t\t\t\t\tdrawing = false;
\t\t\t\t});

\t\t\t\tfunction reset(){
\t\t\t\t\telement[0].width = element[0].width; 
\t\t\t\t}
\t\t      
\t\t\t\tfunction draw(startX, startY, currentX, currentY){
\t\t\t\t\t//reset();
\t\t\t\t\tif (\$scope.tool == 'line'){
\t\t\t\t\t\tctx.moveTo(centerX,centerY);
\t\t\t\t\t\tctx.lineTo(currentX,currentY);
\t\t\t\t\t}
\t\t\t\t\telse if (\$scope.tool == 'rect'){
\t\t\t\t\t\tvar sizeX = currentX - startX;
\t\t\t\t\t\tvar sizeY = currentY - startY;
\t\t\t\t\t\tctx.rect(startX, startY, sizeX, sizeY);
\t\t\t\t\t}
\t\t\t\t\telse if (\$scope.tool == 'circle'){
\t\t\t\t\t\tvar x = (currentX - startX) / 2 + startX ;
\t\t\t\t\t\tvar y = (currentY - startY) / 2 + startY ;
\t\t\t\t\t\tvar r = Math.abs(x - startX);
\t\t\t\t\t\tconsole.log(x+' '+y+' '+r);
\t\t\t\t\t\tctx.arc(x,y,r,0,2*Math.PI);
\t\t\t\t\t}

\t\t\t\t\tctx.lineWidth = 1;
\t\t\t\t\t// color
\t\t\t\t\tctx.strokeStyle = '#000';
\t\t\t\t\t// draw it
\t\t\t\t\tctx.stroke();
\t\t\t\t}
\t\t    }
\t\t  };
\t\t});

\t\tapp.controller(\"whiteboardCtrl\", function (\$scope){
\t\t\t\$scope.tool = \"none\";
\t\t\t\$scope.selectTool = function(selected){
\t\t\t\t\$scope.tool = selected;
\t\t\t};
\t\t\t\$scope.selectAction = function(selected){
\t\t\t\talert('action : '+selected);
\t\t\t};
\t\t});

\t</script>
";
        
        $__internal_8a826a0f2f0d78d168814d85d36162135eedbfa59fd469006e9bdb8efcde9423->leave($__internal_8a826a0f2f0d78d168814d85d36162135eedbfa59fd469006e9bdb8efcde9423_prof);

    }

    // line 130
    public function block_stylesheets($context, array $blocks = array())
    {
        $__internal_bfbd566b00cf07b0990bcbd044116fae6ba8775d2fc83ba680ee173b771f5306 = $this->env->getExtension("native_profiler");
        $__internal_bfbd566b00cf07b0990bcbd044116fae6ba8775d2fc83ba680ee173b771f5306->enter($__internal_bfbd566b00cf07b0990bcbd044116fae6ba8775d2fc83ba680ee173b771f5306_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "stylesheets"));

        // line 131
        echo "    ";
        // line 132
        echo "    <link rel=\"stylesheet\" href=\"//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css\">
    <style type=\"text/css\">
    html, body, .container {
    \twidth: 100%;
    \t background-color: #D0F0F0;
    }
    #menu {
    \tborder:solid black;
\t\tborder-radius: 5px;
\t\tbackground-color: #008080;

\t\t/*min-height: 500px;*/
    }
    #menu > button, #menu > p {
    \tmargin-top: 10px;
    \tmargin-bottom: 10px;
    \tfont-size: 20px;
    }
    #menu > p {
    \tcolor: white;
    }
    #board > div {
    \tborder:solid black;
\t\tborder-radius: 5px;
\t\tbackground-color: #F0F0F0;
\t\twidth: 100%;
\t\theight: auto;
    }
\t#board > div > canvas {
\t\tcursor:crosshair;
\t\twidth: 100%;
\t\theight: auto;
\t}
\t</style>
  ";
        
        $__internal_bfbd566b00cf07b0990bcbd044116fae6ba8775d2fc83ba680ee173b771f5306->leave($__internal_bfbd566b00cf07b0990bcbd044116fae6ba8775d2fc83ba680ee173b771f5306_prof);

    }

    public function getTemplateName()
    {
        return "WhiteBoardBundle:Whiteboard:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  202 => 132,  200 => 131,  194 => 130,  92 => 32,  90 => 31,  84 => 30,  55 => 6,  49 => 5,  37 => 3,  11 => 1,);
    }
}
