using GrappBox.Model.Whiteboard;
using GrappBox.Resources;
using GrappBox.ViewModel;
using System;
using System.Diagnostics;
using System.Threading;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Navigation;
using GrappBox.CustomControler;
using Windows.UI.Xaml;
using System.Collections.Generic;
using Windows.Foundation;
using Windows.UI.Popups;
using GrappBox.Model.Global;

namespace GrappBox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class WhiteBoardView : Page
    {
        private static readonly List<string> buttonsBinding = new List<string>()
        {
            "ms-appx:///Assets/rectangle.png",
            "ms-appx:///Assets/ellipse.png",
            "ms-appx:///Assets/lozenge.png",
            "ms-appx:///Assets/line.png",
            "ms-appx:///Assets/handwrite.png",
            "ms-appx:///Assets/text.png",
            "ms-appx:///Assets/erazer.png",
            "ms-appx:///Assets/pointer.png"
        };
        //Required for navigation
        private readonly NavigationHelper navigationHelper;
        private DispatcherTimer pullTimer;
        private WhiteBoardViewModel wbvm;
        private int whiteboardId;

        public WhiteBoardView()
        {
            try
            {
                this.InitializeComponent();
                MainGrid.Width = Window.Current.Bounds.Width;
            }
            catch (Exception e)
            {
                Debug.WriteLine(e.Message);
            }
            this.DataContext = new ViewModel.WhiteBoardViewModel();
            //Required for navigation
            this.NavigationCacheMode = NavigationCacheMode.Disabled;
            this.navigationHelper = new NavigationHelper(this);
            this.navigationHelper.LoadState += this.NavigationHelper_LoadState;
            this.navigationHelper.SaveState += this.NavigationHelper_SaveState;
        }

        //Required for navigation
        #region NavigationHelper
        public NavigationHelper NavigationHelper
        {
            get { return this.navigationHelper; }
        }

        private void NavigationHelper_LoadState(object sender, LoadStateEventArgs e)
        {
        }

        private void NavigationHelper_SaveState(object sender, SaveStateEventArgs e)
        {
            //pullTimer.Stop();
        }

        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedTo(e);
            whiteboardId = (int)e.Parameter;
            wbvm = this.DataContext as WhiteBoardViewModel;
            await wbvm.OpenWhiteboard(whiteboardId);
            if (wbvm.ObjectsList != null)
            {
                foreach (WhiteboardObject wo in wbvm.ObjectsList)
                {
                    this.drawingCanvas.AddNewElement(wo);
                }
            }/*
            pullTimer = new DispatcherTimer();
            pullTimer.Interval = new TimeSpan(0, 0, 10);
            pullTimer.Tick += PullTimer_Tick;
            pullTimer.Start();*/
        }

        private void PullTimer_Tick(object sender, object e)
        {
            runPull();
        }
        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion

        public async void runPull()
        {
            await wbvm.pullDraw();
            foreach (WhiteboardObject item in wbvm.PullModel.addObjects)
            {
                this.drawingCanvas.AddNewElement(item);
            }
            foreach (WhiteboardObject item in wbvm.PullModel.delObjects)
            {
                this.drawingCanvas.DeleteElement(item.Id);
            }
            wbvm.LastUpdate = DateTime.Now;
        }

        private async void FillcolorBtn_Click(object sender, Windows.UI.Xaml.RoutedEventArgs e)
        {
            Colorpan cp = new Colorpan();
            WhiteboardPopUp.Child = cp;
            WhiteboardPopUp.IsOpen = true;
            WhiteboardPopUp.VerticalOffset = WhiteboardPopUp.ActualHeight;
            await cp.WaitForSelect();
            wbvm.FillColor = cp.SelectedColor;
            WhiteboardPopUp.IsOpen = false;
            WhiteboardPopUp.Child = null;
            Debug.WriteLine(wbvm.FillColor.Color.ToString());
        }

        private async void ColorBtn_Click(object sender, Windows.UI.Xaml.RoutedEventArgs e)
        {
            Colorpan cp = new Colorpan();
            WhiteboardPopUp.Child = cp;
            WhiteboardPopUp.IsOpen = true;
            await cp.WaitForSelect();
            wbvm.StrokeColor = cp.SelectedColor;
            WhiteboardPopUp.IsOpen = false;
            WhiteboardPopUp.Child = null;
            Debug.WriteLine(wbvm.FillColor.Color.ToString());
        }

        private async void ToolsButton_Click(object sender, Windows.UI.Xaml.RoutedEventArgs e)
        {
            ToolPan tp = new ToolPan();
            WhiteboardPopUp.Child = tp;
            WhiteboardPopUp.IsOpen = true;
            await tp.WaitForSelect();
            wbvm.CurrentTool = tp.SelectedTool;
            ToolsButtonIcon.UriSource = new Uri(buttonsBinding[tp.SelectedImage]);
            WhiteboardPopUp.IsOpen = false;
            WhiteboardPopUp.Child = null;
            Debug.WriteLine(wbvm.CurrentTool);
        }
        private async void BrushSizeButton_Click(object sender, RoutedEventArgs e)
        {
            BrushPan bp = new BrushPan();
            WhiteboardPopUp.Child = bp;
            WhiteboardPopUp.IsOpen = true;
            await bp.WaitForSelect();
            wbvm.StrokeThickness = bp.SelectedThickness;
            WhiteboardPopUp.IsOpen = false;
            WhiteboardPopUp.Child = null;
            Debug.WriteLine(wbvm.StrokeThickness);
        }

        private async void drawingCanvas_Tapped(object sender, Windows.UI.Xaml.Input.TappedRoutedEventArgs e)
        {
            if (wbvm.CurrentTool == WhiteboardTool.ERAZER)
            {
                int objectId;
                Point p = e.GetPosition(drawingCanvas);
                objectId = await wbvm.deleteObject(new Model.Position() { X = p.X, Y = p.Y });
                if (objectId != -1)
                {
                    if (drawingCanvas.DeleteElement(objectId) == false)
                    {
                        MessageDialog dialogBox = new MessageDialog("Can't delete this object", "Error");
                        await dialogBox.ShowAsync();
                    }
                }
            }
        }
    }
}