using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Threading.Tasks;
using System.Windows.Input;
using Windows.Foundation;
using Windows.UI;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Media;
using GrappBox.Model;
using GrappBox.CustomControler;
using System.Collections.ObjectModel;
using GrappBox.Model.Whiteboard;
using GrappBox.ApiCom;
using System.Net.Http;
using Windows.UI.Popups;

namespace GrappBox.ViewModel
{
    #region Enum
    public enum WhiteboardTool
    {
        NONE = 0, POINTER, ERAZER, TEXT, RECTANGLE, ELLIPSE, LOZENGE, LINE, HANDWRITING
    }
    #endregion Enum
    class WhiteBoardViewModel : ViewModelBase
    {
        private WhiteBoardModel model;
        public PullModel PullModel { get; private set; }
        public DateTime LastUpdate { get; set; }

        #region BindedPropertiesDeclaration
        private ShapeControler _currentDraw;
        private WhiteboardTool _currentTool;
        private SolidColorBrush _strokeColor;
        private SolidColorBrush _fillColor;
        private double _strokeThickness;
        #endregion BindedPropertiesDeclaration
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

        public WhiteBoardViewModel()
        {
            _currentDraw = null;
            _currentTool = WhiteboardTool.POINTER;
            _strokeThickness = 1;
            _strokeColor = new SolidColorBrush();
            _strokeColor.Color = Colors.Black;
            _fillColor = new SolidColorBrush();
            _fillColor.Color = Colors.Transparent;
        }

        #region CanvasManipCommands
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

        #region Actions

        #region RoutedEventsActions
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
            WhiteboardObject wo = null;
            CurrentDraw.Update(p);
            try
            {
                wo = await pushDraw(ShapeModelConverter.ShapeToModel(CurrentDraw));
                CurrentDraw.Id = wo.Id;
            }
            catch (Exception e)
            {
                Debug.WriteLine(e.Message);
            }
            CurrentDraw = null;
        }
        #endregion RoutedEventsActions

        #endregion Actions

        public ObservableCollection<WhiteboardObject> ObjectsList { get; set; }

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
                LastUpdate = DateTime.Now;
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
            props.Add("lastUpdate", LastUpdate);
            HttpResponseMessage res = await api.Post(props, "whiteboard/pulldraw/" + model.Id);
            Debug.WriteLine(await res.Content.ReadAsStringAsync());
            if (res.IsSuccessStatusCode)
            {
                PullModel = api.DeserializeJson<PullModel>(await res.Content.ReadAsStringAsync());

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
                return tmp;
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            return null;
        }

        public async System.Threading.Tasks.Task<int> deleteObject(Position p)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();
            props.Add("token", User.GetUser().Token);
            props.Add("whiteboardId", model.Id);
            props.Add("center", p);
            props.Add("radius", 15.0);
            HttpResponseMessage res = await api.Put(props, "whiteboard/deleteobject");
            if (res.IsSuccessStatusCode)
            {
                Debug.WriteLine(await res.Content.ReadAsStringAsync());
                WhiteboardObject tmp = api.DeserializeJson<WhiteboardObject>(await res.Content.ReadAsStringAsync());
                Debug.WriteLine("ObjectId= {0}", tmp.Id);
                return tmp.Id;
            }
            else
            {
                Debug.WriteLine("Failed => {0}",await res.Content.ReadAsStringAsync());
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            return -1;
        }
        #endregion API
    }
}