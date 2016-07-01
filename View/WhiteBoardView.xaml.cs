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
        private Timer pullTimer;
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
            drawingCanvas.Tapped += DrawingCanvas_Tapped;
            //Required for navigation
            this.NavigationCacheMode = NavigationCacheMode.Required;
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
            pullTimer.Dispose();
            drawingCanvas.Clear();
        }

        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedTo(e);
            whiteboardId = (int)e.Parameter;
            wbvm = this.DataContext as WhiteBoardViewModel;
            await wbvm.OpenWhiteboard(whiteboardId);
            foreach (WhiteboardObject wo in wbvm.ObjectsList)
            {
                this.drawingCanvas.AddNewElement(wo);
            }
            pullTimer = new Timer(runPull, "pull", TimeSpan.FromSeconds(5), TimeSpan.FromSeconds(5));
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion

        public async void runPull(object source)
        {
            return;
            Debug.WriteLine("RunPull_1");
            await wbvm.pullDraw();
            foreach (WhiteboardObject item in wbvm.PullModel.addObjects)
            {
                Debug.WriteLine("RunPull_2");
                this.drawingCanvas.AddNewElement(item);
                Debug.WriteLine("RunPull_2.5");
            }
            foreach (WhiteboardObject item in wbvm.PullModel.delObjects)
            {
                Debug.WriteLine("RunPull_3");
                this.drawingCanvas.DeleteElement(item.Id);
            }
        }

        private async void FillcolorBtn_Click(object sender, Windows.UI.Xaml.RoutedEventArgs e)
        {
            Colorpan cp = new Colorpan();
            WhiteboardPopUp.Child = cp;
            WhiteboardPopUp.IsOpen = true;
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
            Debug.WriteLine("TOTO");
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

        private void DrawingCanvas_Tapped(object sender, Windows.UI.Xaml.Input.TappedRoutedEventArgs e)
        {

        }
    }
}