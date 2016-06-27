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
using System.Collections.ObjectModel;
using GrappBox.Model.Whiteboard;
using GrappBox.ApiCom;
using GrappBox.Ressources;
using System.Net.Http;
using Windows.UI.Popups;

namespace GrappBox.ViewModel
{
    #region Enum
    public enum WhiteboardTool
    {
        EXPLORE = 0, ERAZER, TEXT, RECTANGLE, ELLIPSE, LOZENGE, LINE, HANDWRITING
    }
    #endregion Enum
    class WhiteBoardViewModel : ViewModelBase
    {
        private WhiteBoardModel model;
        private PullModel _pullModel;
        private ObservableCollection<WhiteboardObject> _objectsList;
        private DateTime _lastUpdate;

        #region BindedPropertiesDeclaration
        private ShapeControler _currentDraw;
        private WhiteboardTool _currentTool;
        private SolidColorBrush _strokeColor;
        private SolidColorBrush _fillColor;
        private double _strokeThickness;
        #endregion BindedPropertiesDeclaration

        public WhiteBoardViewModel()
        {
            _currentDraw = null;
            _currentTool = WhiteboardTool.EXPLORE;
            _strokeThickness = 1;
            _strokeColor = new SolidColorBrush();
            _strokeColor.Color = Colors.Black;
            _fillColor = new SolidColorBrush();
            _fillColor.Color = Colors.Transparent;
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
                    Offset = -350;
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
                    Offset = -280;
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
                    Offset = -200;
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
        private double _offset;
        public double Offset
        {
            get { return _offset; }
            set { _offset = value; NotifyPropertyChanged("Offset"); }
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
        private int _fontSize;
        public int FontSize
        {
            get { return _fontSize; }
            set { _fontSize = value; }
        }
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

        #region BindedPropertiesNotifiers
        public WhiteboardTool CurrentTool
        {
            get { return _currentTool; }
            set { _currentTool = value; NotifyPropertyChanged("CurrentTool"); }
        }
        public ShapeControler CurrentDraw
        {
            get { return _currentDraw; }
            set { _currentDraw = value; NotifyPropertyChanged("CurrentDraw"); }
        }

        public SolidColorBrush StrokeColor
        {
            get { return _strokeColor; }
            set { _strokeColor = value; NotifyPropertyChanged("StrokeColor"); }
        }
        public SolidColorBrush FillColor
        {
            get { return _fillColor; }
            set { _fillColor = value; NotifyPropertyChanged("FillColor"); }
        }
        public double StrokeThickness
        {
            get { return _strokeThickness; }
            set { _strokeThickness = value; NotifyPropertyChanged("StrokeThickness"); }
        }
        #endregion

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
                ShapeControler sc = new ShapeControler(textPos, PopUpTextEntered, IsBold, IsItalic, StrokeColor, 18);
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
            if (CurrentTool == WhiteboardTool.ERAZER)
            {
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
        private async void CanvasManipCompletedAction(Point p)
        {
            if (CurrentDraw == null)
                return;
            CurrentDraw.Update(p);
            WhiteboardObject wo = await pushDraw(ShapeModelConverter.ShapeToModel(CurrentDraw));
            CurrentDraw.Id = wo.Id;
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

        public ObservableCollection<WhiteboardObject> ObjectsList
        {
            get { return _objectsList; }
            set { if (value != null) _objectsList = value; }
        }

        #region API
        public async System.Threading.Tasks.Task OpenWhiteboard(int whiteboardId)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, whiteboardId };
            HttpResponseMessage res = await api.Get(token, "whiteboard/open");
            if (res.IsSuccessStatusCode)
            {
                Debug.WriteLine(await res.Content.ReadAsStringAsync());
                model = api.DeserializeJson<WhiteBoardModel>(await res.Content.ReadAsStringAsync());
                ObjectsList = model.Object;
            }
            else
            {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
        }

        public async System.Threading.Tasks.Task pullDraw()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            props.Add("token", User.GetUser().Token);
            props.Add("lastUpdate", _lastUpdate);
            HttpResponseMessage res = await api.Post(props, "whiteboard/pulldraw/" + model.Id);
            if (res.IsSuccessStatusCode)
            {
                _pullModel = api.DeserializeJson<PullModel>(await res.Content.ReadAsStringAsync());
                foreach (WhiteboardObject item in _pullModel.addObjects)
                {
                    //ajouter les objets au whiteboard
                }
                foreach (WhiteboardObject item in _pullModel.delObjects)
                {
                    //remove les objets au whiteboard
                }
                _lastUpdate = new DateTime();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async Task<WhiteboardObject> pushDraw(ObjectModel om)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();
            props.Add("token", User.GetUser().Token);
            props.Add("modification", "add");
            props.Add("object", om);
            HttpResponseMessage res = await api.Put(props, "whiteboard/pushdraw/" + model.Id);
            if (res.IsSuccessStatusCode)
            {
                Debug.WriteLine(await res.Content.ReadAsStringAsync());
                WhiteboardObject tmp = api.DeserializeJson<WhiteboardObject>(await res.Content.ReadAsStringAsync());
                _objectsList.Add(tmp);
                return tmp;
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            return null;
        }

        public async System.Threading.Tasks.Task deleteObject()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();
            props.Add("token", User.GetUser().Token);
            props.Add("whiteboardId", model.Id);
            //props.Add("center", ); centre du pinceau
            //props.Add("radius", ); radius du pinceau
            HttpResponseMessage res = await api.Put(props, "whiteboard/pushdraw/" + model.Id);
            if (res.IsSuccessStatusCode)
            {
                WhiteboardObject tmp = api.DeserializeJson<WhiteboardObject>(await res.Content.ReadAsStringAsync());
                _objectsList.Remove(tmp);
                //retirer à la map? + delete objet sur canvas
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }
        #endregion API
    }
}