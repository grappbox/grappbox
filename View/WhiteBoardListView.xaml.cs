using GrappBox.Model;
using GrappBox.Resources;
using GrappBox.ViewModel;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI.Core;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class WhiteBoardListView : Page
    {
        //Required for navigation
        private readonly NavigationHelper navigationHelper;

        public WhiteBoardListView()
        {
            this.InitializeComponent();
            this.DataContext = new WhiteBoardListViewModel();

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

        }

        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedTo(e);
            WhiteBoardListViewModel tmp = this.DataContext as WhiteBoardListViewModel;
            await tmp.GetWhiteboards();
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion


        private async void AppBarButton_Click(object sender, RoutedEventArgs e)
        {
            Popup.IsOpen = true;
            WhiteBoardListViewModel wblm = this.DataContext as WhiteBoardListViewModel;
            if (WhiteboardNameInput.ShowDialog() == true)
            {
                await wblm.CreateWhiteboard(WhiteboardNameInput.ResultText);
            }
        }

        private async void ListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            ListView lv = sender as ListView;
            WhiteBoardListModel wblm = lv.SelectedItem as WhiteBoardListModel;
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () =>
                Frame.Navigate(typeof(WhiteBoardView), wblm.Id));
        }
    }
}
