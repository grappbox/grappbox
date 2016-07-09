using GrappBox.ApiCom;
using GrappBox.Model;
using GrappBox.Resources;
using GrappBox.ViewModel;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.ApplicationModel.Core;
using Windows.Foundation;
using Windows.Foundation.Collections;
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
    public sealed partial class TimelineMessageView : Page
    {
        CoreApplicationView view;
        TimelineViewModel vm = TimelineViewModel.GetViewModel();

        //Required for navigation
        private readonly NavigationHelper navigationHelper;

        static private TimelineMessageView instance = null;
        static public TimelineMessageView GetInstance()
        {
            if (instance == null)
                instance = new TimelineMessageView();
            return instance;
        }
        public TimelineMessageView()
        {
            this.InitializeComponent();
            instance = this;
            view = CoreApplication.GetCurrentView();
            this.DataContext = vm;

            //Required for navigation
            this.NavigationCacheMode = NavigationCacheMode.Required;
            this.navigationHelper = new NavigationHelper(this);
            this.navigationHelper.LoadState += this.NavigationHelper_LoadState;
            this.navigationHelper.SaveState += this.NavigationHelper_SaveState;
        }

        //Required for navigation
        #region NavigationHelper
        /// <summary>
        /// Gets the <see cref="NavigationHelper"/> associated with this <see cref="Page"/>.
        /// </summary>
        public NavigationHelper NavigationHelper
        {
            get { return this.navigationHelper; }
        }

        /// <summary>
        /// Populates the page with content passed during navigation. Any saved state is also
        /// provided when recreating a page from a prior session.
        /// </summary>
        /// <param name="sender">
        /// The source of the event; typically <see cref="NavigationHelper"/>.
        /// </param>
        /// <param name="e">Event data that provides both the navigation parameter passed to
        /// <see cref="Frame.Navigate(Type, Object)"/> when this page was initially requested and
        /// a dictionary of state preserved by this page during an earlier
        /// session. The state will be null the first time a page is visited.</param>
        private void NavigationHelper_LoadState(object sender, LoadStateEventArgs e)
        {

        }

        /// <summary>
        /// Preserves state associated with this page in case the application is suspended or the
        /// page is discarded from the navigation cache. Values must conform to the serialization
        /// requirements of <see cref="SuspensionManager.SessionState"/>.
        /// </summary>
        /// <param name="sender">The source of the event; typically <see cref="NavigationHelper"/>.</param>
        /// <param name="e">Event data that provides an empty dictionary to be populated with
        /// serializable state.</param>
        private void NavigationHelper_SaveState(object sender, SaveStateEventArgs e)
        {

        }

        /// <summary>
        /// The methods provided in this section are simply used to allow
        /// NavigationHelper to respond to the page's navigation methods.
        /// <para>
        /// Page specific logic should be placed in event handlers for the  
        /// <see cref="NavigationHelper.LoadState"/>
        /// and <see cref="NavigationHelper.SaveState"/>.
        /// The navigation parameter is available in the LoadState method 
        /// in addition to page state preserved during an earlier session.
        /// </para>
        /// </summary>
        /// <param name="e">Provides data for navigation methods and event
        /// handlers that cannot cancel the navigation request.</param>
        protected override void OnNavigatedTo(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedTo(e);
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion

        private async void EditMessage_Click(object sender, RoutedEventArgs e)
        {
            vm.CommentSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.CommentSelected != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.updateMessage(vm.CommentSelected);

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private async void DeleteMessage_Click(object sender, RoutedEventArgs e)
        {
            vm.CommentSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.CommentSelected != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.removeMessage(vm.CommentSelected);

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private async void PostComment_Click(object sender, RoutedEventArgs e)
        {
            if (CommentTitle.Text != "" && CommentMessage.Text != "")
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.postMessage(vm.MessageSelected.TimelineId, CommentTitle.Text, CommentMessage.Text, vm.MessageSelected.Id);
                CommentTitle.Text = "";
                CommentMessage.Text = "";
                PostComPopUp.Visibility = Visibility.Collapsed;
                CommentsListView.IsEnabled = true;

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private void Add_Click(object sender, RoutedEventArgs e)
        {
            PostComPopUp.Visibility = Visibility.Visible;
            CommentsListView.IsEnabled = false;
        }

        private void Cancel_Click(object sender, RoutedEventArgs e)
        {
            PostComPopUp.Visibility = Visibility.Collapsed;
            CommentsListView.IsEnabled = true;
        }
    }
}
