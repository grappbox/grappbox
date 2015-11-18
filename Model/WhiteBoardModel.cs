using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.UI;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;

namespace GrappBox.Model
{
    #region Enum
    enum WhiteboardTool
    {
        EXPLORE = 0, ERAZER, TEXT, RECTANGLE, ELLIPSE, LOZENGE, LINE, HANDWRITING
    }
    #endregion Enum
    class WhiteBoardModel
    {
        #region BindedPropertiesDeclaration
        private ShapeControler _currentDraw;
        private WhiteboardTool _currentTool;
        #endregion BindedPropertiesDeclaration
        public WhiteboardTool CurrentTool
        {
            get { return _currentTool; }
            set { _currentTool = value;}
        }
        public ShapeControler CurrentDraw
        {
            get { return _currentDraw; }
            set { _currentDraw = value;}
        }
        private SolidColorBrush _strokeColor;
        private SolidColorBrush _fillColor;
        private double _strokeThickness;
        public SolidColorBrush StrokeColor
        {
            get { return _strokeColor; }
            set { _strokeColor = value; }
        }
        public SolidColorBrush FillColor
        {
            get { return _fillColor; }
            set { _fillColor = value; }
        }
        public double StrokeThickness
        {
            get { return _strokeThickness; }
            set { _strokeThickness = value; }
        }
        public WhiteBoardModel()
        {
            _currentDraw = null;
            _currentTool = WhiteboardTool.EXPLORE;
            _strokeThickness = 1;
            _strokeColor = new SolidColorBrush();
            _strokeColor.Color = Colors.Black;
            _fillColor = new SolidColorBrush();
            _fillColor.Color = Colors.Transparent;
        }
    }
}
