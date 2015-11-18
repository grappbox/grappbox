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
using Windows.UI.Xaml;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using GrappBox.Model;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Shapes;
using GrappBox.CustomControler;
using Windows.UI.Xaml.Controls;

namespace GrappBox.ViewModel
{
    class WhiteBoardViewModel : ViewModelBase
    {

        private WhiteBoardModel model;
        public WhiteBoardViewModel()
        {
            model = new WhiteBoardModel();
        }

        #region ColorPansLogic
        private ColorMod _selectedColormod;
        public ColorMod SelectedColorMod
        {
            get { return _selectedColormod; }
            set { _selectedColormod = value; NotifyPropertyChanged("SelectedColorMod"); }
        }
        private bool _colorPanOpened = false;
        public bool ColorPanOpened
        {
            get { return _colorPanOpened; }
            set
            {
                _colorPanOpened = value;
                NotifyPropertyChanged("ColorPanOpened");
                if (value)
                {
                    if (FillColorPanOpened)
                        FillColorPanOpened = false;
                    if (BrushPanOpened)
                        BrushPanOpened = false;
                    ColorPanVisible = Visibility.Visible;
                }
                else
                    ColorPanVisible = FillColorPanOpened ? Visibility.Visible : Visibility.Collapsed;
            }
        }
        private bool _fillColorPanOpened = false;
        public bool FillColorPanOpened
        {
            get { return _fillColorPanOpened; }
            set
            {
                _fillColorPanOpened = value;
                NotifyPropertyChanged("FillColorPanOpened");
                if (value)
                {
                    if (ColorPanOpened)
                        ColorPanOpened = false;
                    if (BrushPanOpened)
                        BrushPanOpened = false;
                    ColorPanVisible = Visibility.Visible;
                }
                else
                    ColorPanVisible = ColorPanOpened ? Visibility.Visible : Visibility.Collapsed;
            }
        }
        private bool _brushPanOpened = false;
        public bool BrushPanOpened
        {
            get { return _brushPanOpened; }
            set
            {
                _brushPanOpened = value;
                NotifyPropertyChanged("BrushPanOpened");
                if (value)
                {
                    if (ColorPanOpened || FillColorPanOpened)
                    {
                        ColorPanOpened = false;
                        FillColorPanOpened = false;
                    }
                    BrushPanVisible = Visibility.Visible;
                }
                else
                    BrushPanVisible = Visibility.Collapsed;
            }
        }
        private Visibility _brushPanVisible = Visibility.Collapsed;
        public Visibility BrushPanVisible
        {
            get { return _brushPanVisible; }
            set
            {
                _brushPanVisible = value;
                NotifyPropertyChanged("BrushPanVisible");
                if (value == Visibility.Collapsed)
                {
                    _brushPanOpened = false;
                    NotifyPropertyChanged("BrushPanOpened");
                }
                if (ColorPanVisible == Visibility.Visible)
                    ColorPanVisible = Visibility.Collapsed;
            }
        }
        private Visibility _colorPanVisible = Visibility.Collapsed;
        public Visibility ColorPanVisible
        {
            get { return _colorPanVisible; }
            set
            {
                _colorPanVisible = value; NotifyPropertyChanged("ColorPanVisible");
                if (value == Visibility.Collapsed)
                {
                    _colorPanOpened = false;
                    _fillColorPanOpened = false;
                    NotifyPropertyChanged("FillColorPanOpened");
                    NotifyPropertyChanged("ColorPanOpened");
                }
                if (BrushPanVisible == Visibility.Visible)
                    BrushPanVisible = Visibility.Collapsed;
            }
        }
        #endregion ColorPansLogic

        #region TextPanLogic
        private Point textPos;
        private bool _textPanOpened = false;
        public bool TextPanOpened
        {
            get { return _textPanOpened; }
            set { _textPanOpened = value;  NotifyPropertyChanged("TextPanOpened"); }
        }
        private bool _popUpTextConfirmed = false;
        public bool PopUpTextConfirmed
        {
            get { return _popUpTextConfirmed; }
            set { _popUpTextConfirmed = value; NotifyPropertyChanged("PopUpTextConfirmed"); TextPanTappedAction(value); }
        }
        private string _popUpTextEntered;
        public string PopUpTextEntered
        {
            get { return _popUpTextEntered; }
            set { _popUpTextEntered = value; }
        }
        private bool _isBold;
        public bool IsBold
        {
            get { return _isBold; }
            set { _isBold = value; NotifyPropertyChanged("IsBold"); }
        }
        private bool _isItalic;
        public bool IsItalic
        {
            get { return _isItalic; }
            set { _isItalic = value; NotifyPropertyChanged("IsItalic"); }
        }
        #endregion TextPanLogic

        #region ModelBindedPropertiesNotifiers
        public WhiteboardTool CurrentTool
        {
            get { return model.CurrentTool; }
            set { model.CurrentTool = value; NotifyPropertyChanged("CurrentTool"); }
        }
        public ShapeControler CurrentDraw
        {
            get { return model.CurrentDraw; }
            set { model.CurrentDraw = value; NotifyPropertyChanged("CurrentDraw"); }
        }
        public SolidColorBrush StrokeColor
        {
            get { return model.StrokeColor; }
            set { model.StrokeColor = value; NotifyPropertyChanged("StrokeColor"); }
        }
        public SolidColorBrush FillColor
        {
            get { return model.FillColor; }
            set { model.FillColor = value; NotifyPropertyChanged("FillColor"); }
        }
        public double StrokeThickness
        {
            get { return model.StrokeThickness; }
            set { model.StrokeThickness = value; NotifyPropertyChanged("StrokeThickness"); }
        }
        #endregion ModelBindedPropertiesNotifiers

        #region Commands
        #region toolsCommand
        private ICommand _toolCommand;
        public ICommand ToolCommand
        {
            get { return _toolCommand ?? (_toolCommand = new CommandHandler<WhiteboardTool>(ToolAction)); }
        }
        private ICommand _colorButtonTapped;
        public ICommand ColorButtonTapped
        {
            get { return _colorButtonTapped ?? (_colorButtonTapped = new CommandHandler<ColorMod>(ColorPanTappedAction)); }
        }
        #endregion toolsCommand
        #region CanvasManipCommands
        private ICommand _canvasTappedCommand;
        public ICommand CanvasTappedCommand
        {
            get { return _canvasTappedCommand ?? (_canvasTappedCommand = new CommandHandler<Point>(CanvasTappedAction)); }
        }
        private ICommand _canvasManipStartedCommand;
        public ICommand CanvasManipStartedCommand
        {
            get { return _canvasManipStartedCommand ?? (_canvasManipStartedCommand = new CommandHandler<Point>(CanvasManipStartedAction)); }
        }
        private ICommand _canvasManipDeltaCommand;
        public ICommand CanvasManipDeltaCommand
        {
            get { return _canvasManipDeltaCommand ?? (_canvasManipDeltaCommand = new CommandHandler<Point>(CanvasManipDeltaAction)); }
        }
        private ICommand _canvasManipCompletedCommand;
        public ICommand CanvasManipCompletedCommand
        {
            get { return _canvasManipCompletedCommand ?? (_canvasManipCompletedCommand = new CommandHandler<Point>(CanvasManipCompletedAction)); }
        }
        #endregion CanvasManipCommands
        #endregion Commands

        #region Actions

        #region RoutedEventsActions
        private void TextPanTappedAction(bool val)
        {
            if (val == true)
            {
                Debug.WriteLine("JE FAIS DE LA MERDE !!!!!!!!!!");
                ShapeControler sc = new ShapeControler(textPos, PopUpTextEntered, IsBold, IsItalic, StrokeColor);
                CurrentDraw = sc;
            }
        }
        private void CanvasTappedAction(Point p)
        {
            if (CurrentTool == WhiteboardTool.TEXT && TextPanOpened == false)
            {
                textPos = p;
                TextPanOpened = true;
            }
        }
        private void ColorPanTappedAction(ColorMod mod)
        {
            Debug.WriteLine("ColorPanTappedAction {0}", mod);
            SelectedColorMod = mod;
        }
        private void CanvasHoldAction(Point p)
        {
            Debug.WriteLine("CanvasHoldAction");
            Debug.WriteLine(p.X.ToString() + " " + p.Y.ToString());
        }
        private void CanvasManipStartedAction(Point p)
        {
            if (CurrentTool < WhiteboardTool.RECTANGLE)
                return;
            ShapeControler sc = new ShapeControler(CurrentTool, p, StrokeColor, FillColor, StrokeThickness);
            CurrentDraw = sc;
        }
        private void CanvasManipDeltaAction(Point p)
        {
            if (CurrentDraw == null)
                return;
            if (CurrentTool >= WhiteboardTool.RECTANGLE)
                CurrentDraw.Update(p);
        }
        private void CanvasManipCompletedAction(Point p)
        {
            if (CurrentDraw == null)
                return;
            CurrentDraw.Update(p);
            CurrentDraw = null;
        }
        #endregion RoutedEventsActions

        #region ToolsBoxActions
        private void ToolAction(WhiteboardTool val)
        {
            CurrentTool = val;
            Debug.WriteLine("TOOL SELECTION => {0}", val);
        }
        #endregion ToolBoxActions

        #endregion Actions
    }
}