using GrappBox.Model.Whiteboard;
using GrappBox.Resources;
using GrappBox.ViewModel;
using System;
using System.Diagnostics;
using System.Threading;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Navigation;


namespace GrappBox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class WhiteBoardView : Page
    {
        //Required for navigation
        private readonly NavigationHelper navigationHelper;
        private Timer pullTimer;
        private WhiteBoardViewModel wbvm;

        public WhiteBoardView()
        {
            try
            {
                this.InitializeComponent();
            }
            catch (Exception e)
            {
                Debug.WriteLine(e.Message);
            }
            this.DataContext = new ViewModel.WhiteBoardViewModel();

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
        }
        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedTo(e);
            int id = (int)e.Parameter;
            wbvm = this.DataContext as WhiteBoardViewModel;
            await wbvm.OpenWhiteboard(id);
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
            await wbvm.pullDraw();
        }
    }
}