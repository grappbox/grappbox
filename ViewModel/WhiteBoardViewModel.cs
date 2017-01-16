using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Threading.Tasks;
using System.Windows.Input;
using Windows.Foundation;
using Windows.UI;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Media;
using Grappbox.Model;
using Grappbox.CustomControls;
using System.Collections.ObjectModel;
using Grappbox.Model.Whiteboard;
using Grappbox.HttpRequest;
using Windows.Web.Http;
using Windows.UI.Popups;
using Grappbox.Helpers;
using Newtonsoft.Json;
using Windows.UI.Input;
using Windows.UI.Xaml.Input;
using Windows.Networking.PushNotifications;

namespace Grappbox.ViewModel
{
    #region Enum
    public enum WhiteboardTool
    {
        NONE = 0, POINTER, ERAZER, TEXT, RECTANGLE, ELLIPSE, LOZENGE, LINE, HANDWRITING
    }
    #endregion Enum
    public class WhiteBoardViewModel : ViewModelBase
    {
        public WhiteBoardModel model;
        public PullModel PullModel { get; set; }
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

        public void CanvasManipStartedAction(object sender, Windows.UI.Xaml.Input.ManipulationStartedRoutedEventArgs e)
        {
            var p = e.Position;
            if (CurrentTool < WhiteboardTool.RECTANGLE)
                return;
            ShapeControler sc = new ShapeControler(CurrentTool, p, StrokeColor, FillColor, StrokeThickness);
            CurrentDraw = sc;
        }

        public void CanvasManipDeltaAction(object sender, ManipulationDeltaRoutedEventArgs e)
        {
            var p = e.Position;
            if (CurrentDraw == null)
                return;
            if (CurrentTool >= WhiteboardTool.RECTANGLE)
                CurrentDraw.Update(p);
        }

        public async void CanvasManipCompletedAction(object sender, Windows.UI.Xaml.Input.ManipulationCompletedRoutedEventArgs e)
        {
            var p = e.Position;
            if (CurrentDraw == null)
                return;
            WhiteboardObject wo = null;
            CurrentDraw.Update(p);
            try
            {
                wo = await pushDraw(ShapeModelConverter.ShapeToModel(CurrentDraw));
                CurrentDraw.Id = wo.Id;
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
            }
            CurrentDraw = null;
        }

        public ObservableCollection<WhiteboardObject> ObjectsList { get; set; }
        #region API
        public async System.Threading.Tasks.Task<bool> OpenWhiteboard(int whiteboardId)
        {
            HttpResponseMessage res = await HttpRequestManager.Get(Constants.OpenWhiteboards, whiteboardId);
            if (res.IsSuccessStatusCode)
            {
                string json = await res.Content.ReadAsStringAsync();
                Debug.WriteLine(json);
                JsonSerializerSettings settings = new JsonSerializerSettings()
                {
                    DateFormatString = "yyyy-MM-dd HH:mm:ss"
                };
                try
                {
                    model = SerializationHelper.DeserializeJson<WhiteBoardModel>(json, settings);
                    ObjectsList = model.Object;
                    LastUpdate = DateTime.Now;
                }
                catch
                {
                    return false;
                }
            }
            else
            {
                Debug.WriteLine("Can't open whiteboard");
                return false;
            }
            return true;
        }

        public async Task<WhiteboardObject> pushDraw(ObjectModel om)
        {
            Dictionary<string, object> props = new Dictionary<string, object>();
            props.Add("modification", "add");
            props.Add("object", om);
            HttpResponseMessage res = await HttpRequestManager.Put(props, "whiteboard/draw/" + model.Id);
            if (res.IsSuccessStatusCode)
            {
                Debug.WriteLine(await res.Content.ReadAsStringAsync());
                WhiteboardObject tmp = SerializationHelper.DeserializeJson<WhiteboardObject>(await res.Content.ReadAsStringAsync());
                return tmp;
            }
            else
            {
                MessageDialog msgbox = new MessageDialog("Can't create object");
                await msgbox.ShowAsync();
            }
            return null;
        }

        public async System.Threading.Tasks.Task<int> deleteObject(Position p)
        {
            Dictionary<string, object> props = new Dictionary<string, object>();
            props.Add("center", p);
            props.Add("radius", 15.0);
            HttpResponseMessage res = await HttpRequestManager.Put(props, Constants.DeleteWhiteboardObject + model.Id);
            if (res.IsSuccessStatusCode)
            {
                string json = await res.Content.ReadAsStringAsync();
                Debug.WriteLine(json);
                WhiteboardObject tmp = SerializationHelper.DeserializeJson<WhiteboardObject>(json);
                Debug.WriteLine("ObjectId= {0}", tmp.Id);
                return tmp.Id;
            }
            else
            {
                Debug.WriteLine("Failed => {0}", await res.Content.ReadAsStringAsync());
                MessageDialog msgbox = new MessageDialog("Can't delete object");
                await msgbox.ShowAsync();
            }
            return -1;
        }
        #endregion API
    }
}