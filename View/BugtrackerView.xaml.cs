using GrappBox.Model;
using GrappBox.ViewModel;
using System;
using System.Collections.Generic;
using System.Diagnostics;
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
using GrappBox.Resources;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class BugtrackerView : Page
    {
        private NavigationHelper navigationHelper;
        CoreApplicationView view;
        BugtrackerViewModel vm = BugtrackerViewModel.GetViewModel();

        static private BugtrackerView instance = null;
        static public BugtrackerView GetInstance()
        {
            return instance;
        }
        public BugtrackerView()
        {
            this.InitializeComponent();
            instance = this;
            view = CoreApplication.GetCurrentView();
            this.DataContext = vm;
            this.NavigationCacheMode = NavigationCacheMode.Required;

            this.navigationHelper = new NavigationHelper(this);
            this.navigationHelper.LoadState += this.NavigationHelper_LoadState;
            this.navigationHelper.SaveState += this.NavigationHelper_SaveState;
        }

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
        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            this.navigationHelper.OnNavigatedTo(e);
            vm.OpenSelect = null;
            OpenListView.SelectedItem = null;
            CloseListView.SelectedItem = null;
            vm.CloseSelect = null;

            await vm.getOpenTickets();
            await vm.getClosedTickets();
            await vm.getStateList();
            await vm.getTagList();
            await vm.getUsers();

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion

        private void AddBug_Click(object sender, RoutedEventArgs e)
        {
            this.Frame.Navigate(typeof(BugView), null);
        }

        private async void CloseBug_Click(object sender, RoutedEventArgs e)
        {
            if (vm.OpenSelect != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.closeTicket();
                CloseBug.Visibility = Visibility.Collapsed;

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private async void ReopenBug_Click(object sender, RoutedEventArgs e)
        {
            if (vm.CloseSelect != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                await vm.reopenTicket();
                ReopenBug.Visibility = Visibility.Collapsed;

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }

        private void openListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            if ((sender as ListBox).SelectedItem != null)
            {
                vm.OpenSelect = (sender as ListBox).SelectedItem as BugtrackerModel;
                CloseBug.Visibility = Visibility.Visible;
            }
            else
                CloseBug.Visibility = Visibility.Collapsed;
        }

        private void closeListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            if ((sender as ListBox).SelectedItem != null)
            {
                vm.CloseSelect = (sender as ListBox).SelectedItem as BugtrackerModel;
                ReopenBug.Visibility = Visibility.Visible;
            }
            else
                ReopenBug.Visibility = Visibility.Collapsed;
        }

        private void Pivot_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            int num = Pivot.SelectedIndex;
            vm.OpenSelect = null;
            OpenListView.SelectedItem = null;
            CloseListView.SelectedItem = null;
            vm.CloseSelect = null;

            if (num == 1)
            {
                EditOpenBug.Visibility = Visibility.Visible;
                EditCloseBug.Visibility = Visibility.Collapsed;
            }
            else if (num == 2)
            {
                EditOpenBug.Visibility = Visibility.Collapsed;
                EditCloseBug.Visibility = Visibility.Visible;
            }
        }

        private async void EditOpenBug_Click(object sender, RoutedEventArgs e)
        {
            if (vm.OpenSelect != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                vm.getTicket(vm.OpenSelect);
                await vm.getComments();

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
                this.Frame.Navigate(typeof(BugView), vm.OpenSelect.Id);
            }
        }

        private async void EditCloseBug_Click(object sender, RoutedEventArgs e)
        {
            if (vm.CloseSelect != null)
            {
                LoadingBar.IsEnabled = true;
                LoadingBar.Visibility = Visibility.Visible;

                vm.getTicket(vm.CloseSelect);
                await vm.getComments();

                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;
            }
        }
    }
}
