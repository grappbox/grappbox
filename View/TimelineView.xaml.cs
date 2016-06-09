using GrappBox.ApiCom;
using GrappBox.Model;
using GrappBox.Resources;
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

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class TimelineView : Page
    {
        CoreApplicationView view;
        TimelineViewModel vm = TimelineViewModel.GetViewModel();
        List<Border> items = new List<Border>();

        //Required for navigation
        private readonly NavigationHelper navigationHelper;

        static private TimelineView instance = null;
        static public TimelineView GetInstance()
        {
            return instance;
        }
        public TimelineView()
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
            vm.getCustomerMessages();
            vm.getTeamMessages();
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion

        #region Selection changed
        private void ListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.MessageSelected = (sender as ListBox).SelectedItem as TimelineModel;
        }

        private void Pivot_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            int num = Pivot.SelectedIndex;

            if (num == 0)
            {
                AddTeamMessage.Visibility = Visibility.Visible;
                AddCustomerMessage.Visibility = Visibility.Collapsed;
            }
            else if (num == 1)
            {
                AddTeamMessage.Visibility = Visibility.Collapsed;
                AddCustomerMessage.Visibility = Visibility.Visible;
            }
        }
        #endregion

        #region Click
        private void PostTeamMessage_Click(object sender, RoutedEventArgs e)
        {
            if (MessageTitle.Text != "" && Message.Text != "")
            {
                vm.postMessage(vm.TeamId, MessageTitle.Text, Message.Text);
                Pivot.IsLocked = false;
                PostTeamMesPopUp.Visibility = Visibility.Collapsed;
                TeamListView.IsEnabled = true;
            }
        }

        private void PostCustomerMessage_Click(object sender, RoutedEventArgs e)
        {
            if (CustomerTitle.Text != "" && CustomerMessage.Text != "")
            {
                vm.postMessage(vm.CustomerId, CustomerTitle.Text, CustomerMessage.Text);
                Pivot.IsLocked = false;
                PostCustomerMesPopUp.Visibility = Visibility.Collapsed;
                CustomerListView.IsEnabled = true;
            }
        }

        private void EditMessage_Click(object sender, RoutedEventArgs e)
        {
            vm.MessageSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.MessageSelected != null)
                vm.updateMessage(vm.MessageSelected);
        }

        private void DeleteMessage_Click(object sender, RoutedEventArgs e)
        {
            vm.MessageSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.MessageSelected != null)
                vm.removeMessage(vm.MessageSelected);
        }

        private void Comments_Click(object sender, RoutedEventArgs e)
        {
            vm.MessageSelected = (sender as Button).DataContext as TimelineModel;
            vm.getComments(vm.MessageSelected.TimelineId, vm.MessageSelected.Id);
            this.Frame.Navigate(typeof(TimelineMessageView));
        }

        private void Bug_Click(object sender, RoutedEventArgs e)
        {
            BugtrackerViewModel bvm = BugtrackerViewModel.GetViewModel();
            if (bvm == null)
                bvm = new BugtrackerViewModel();
            bvm.getStateList();
            bvm.getTagList();
            bvm.getUsers();
            vm.MessageSelected = (sender as Button).DataContext as TimelineModel;
            bvm.Title = vm.MessageSelected.Title;
            bvm.Description = vm.MessageSelected.Message;
            this.Frame.Navigate(typeof(BugView));
        }

        private void CancelTeam_Click(object sender, RoutedEventArgs e)
        {
            Pivot.IsLocked = false;
            PostTeamMesPopUp.Visibility = Visibility.Collapsed;
            TeamListView.IsEnabled = true;
        }

        private void AddTeam_Click(object sender, RoutedEventArgs e)
        {
            Pivot.IsLocked = true;
            PostTeamMesPopUp.Visibility = Visibility.Visible;
            TeamListView.IsEnabled = false;
        }

        private void CancelCustomer_Click(object sender, RoutedEventArgs e)
        {
            Pivot.IsLocked = false;
            PostCustomerMesPopUp.Visibility = Visibility.Collapsed;
            CustomerListView.IsEnabled = true;
        }

        private void AddCustomer_Click(object sender, RoutedEventArgs e)
        {
            Pivot.IsLocked = true;
            PostCustomerMesPopUp.Visibility = Visibility.Visible;
            CustomerListView.IsEnabled = false;
        }
        #endregion

        private void StackPanel_Loaded(object sender, RoutedEventArgs e)
        {
            TimelineModel currentModel = (sender as StackPanel).DataContext as TimelineModel;

            if (currentModel.Creator.Id != User.GetUser().Id)
                ((sender as StackPanel).Parent as ListBoxItem).IsEnabled = false;
            foreach (var item in (sender as StackPanel).Children)
            {
                if (item as TextBlock != null && (item as TextBlock).Name == "block")
                {
                    if (currentModel.EditedAt != null)
                        (item as TextBlock).Text = "Edited By " + currentModel.Creator.Fullname + " at " + DateTime.Parse(currentModel.EditedAt.date);
                    else
                        (item as TextBlock).Text = "Created By " + currentModel.Creator.Fullname + " at " + DateTime.Parse(currentModel.CreatedAt.date);
                }
            }
        }
    }
}
