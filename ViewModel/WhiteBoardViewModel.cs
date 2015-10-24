using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Input;
using Windows.Foundation;
using Windows.UI;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;

namespace GrappBox.ViewModel
{
    class WhiteBoardViewModel : ViewModelBase
    {
        private shapeType _shapeType;
        private string _customProp;
        public string CustomProp
        {
            get { return _customProp; }
            set { _customProp = value;
                NotifyPropertyChanged("CustomProp");
            }
        }
        private List<ShapeControler> _shapeList;
        public List<ShapeControler> ShapeList
        {
            get { return _shapeList; }
            set
            {
                _shapeList = value;
                NotifyPropertyChanged("ShapeList");
            }
        }
        public WhiteBoardViewModel()
        {
            ShapeList = new List<ShapeControler>();
            _shapeType = shapeType.RECTANGLE;
        }

        #region Commands
        private ICommand _rectangleCommand;
        public ICommand RectangleCommand
        {
            get
            {
                return _rectangleCommand ?? (_rectangleCommand = new CommandHandler(() => RectangleAction()));
            }
        }
        private ICommand _circleCommand;
        public ICommand CircleCommand
        {
            get
            {
                return _circleCommand ?? (_circleCommand = new CommandHandler(CircleAction));
            }
        }
        private ICommand _lineCommand;
        public ICommand LineCommand
        {
            get
            {
                return _lineCommand ?? (_lineCommand = new CommandHandler(() => LineAction()));
            }
        }
        private ICommand _canvasTappedCommand;
        public ICommand CanvasTappedCommand
        {
            get
            {
                return _canvasTappedCommand ?? (_canvasTappedCommand = new CommandHandler<Point>(CanvasTappedAction));
            }
        }
        
        #endregion Commands

        #region Actions
        private void CanvasTappedAction(Point p)
        {
            var toto = new List<ShapeControler>(ShapeList);

            toto.Add(new ShapeControler(p, this._shapeType));
            Debug.WriteLine("CanvasTappedAction");
            Debug.WriteLine(p.X.ToString() + " " + p.Y.ToString());
            ShapeList = toto;
            CustomProp = p.X.ToString() + " " + p.Y.ToString();
        }
        private void RectangleAction()
        {
            this._shapeType = shapeType.RECTANGLE;
            Debug.WriteLine("rectangleAction");
        }
        private void CircleAction()
        {
            this._shapeType = shapeType.CIRCLE;
            Debug.WriteLine("circleAction");
        }
        private void LineAction()
        {
            this._shapeType = shapeType.LINE;
            Debug.WriteLine("lineAction");
        }
        #endregion Actions
    }
}
