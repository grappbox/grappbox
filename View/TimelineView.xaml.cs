using GrappBox.ApiCom;
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
        }

        /// <summary>
        /// Invoked when this page is about to be displayed in a Frame.
        /// </summary>
        /// <param name="e">Event data that describes how this page was reached.
        /// This parameter is typically used to configure the page.</param>
        protected override void OnNavigatedTo(NavigationEventArgs e)
        {
            vm.getCustomerMessages();
            vm.getTeamMessages();
        }

        #region menuClicked
        private void WhiteboardButton_Click(object sender, RoutedEventArgs e)
        {
            this.Frame.Navigate(typeof(WhiteBoardView));
        }

        private void UserSettingsButton_Click(object sender, RoutedEventArgs e)
        {
            UserSettingsViewModel usvm = new UserSettingsViewModel();
            usvm.getAPI();
            this.Frame.Navigate(typeof(UserView));
        }

        private void DashboardButton_Click(object sender, RoutedEventArgs e)
        {
            this.Frame.Navigate(typeof(DashBoardView));
        }

        private void ProjectSettingsButton_Click(object sender, RoutedEventArgs e)
        {
            ProjectSettingsViewModel psvm = new ProjectSettingsViewModel();
            psvm.getProjectSettings();
            psvm.getProjectUsers();
            psvm.getCustomerAccesses();
            psvm.getRoles();
            this.Frame.Navigate(typeof(ProjectSettingsView));
        }

        private void BugtrackerButton_Click(object sender, RoutedEventArgs e)
        {
            BugtrackerViewModel vm = new BugtrackerViewModel();
            vm.getOpenTickets();
            vm.getClosedTickets();
            vm.getStateList();
            vm.getTagList();
            vm.getUsers();
            this.Frame.Navigate(typeof(BugtrackerView));
        }
        private void TimelineButton_Click(object sender, RoutedEventArgs e)
        {
            TimelineViewModel vm = new TimelineViewModel();
            vm.getTimelines();
        }
        #endregion menuClicked

        #region Selection changed
        private void ListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.MessageSelected = (sender as ListBox).SelectedItem as TimelineModel;
        }
        #endregion

        #region Click
        private void PostTeamMessage_Click(object sender, RoutedEventArgs e)
        {
            if (MessageTitle.Text != "" && Message.Text != "")
                vm.postMessage(vm.TeamId, MessageTitle.Text, Message.Text);
        }

        private void PostCustomerMessage_Click(object sender, RoutedEventArgs e)
        {
            if (CustomerTitle.Text != "" && CustomerMessage.Text != "")
                vm.postMessage(vm.CustomerId, CustomerTitle.Text, CustomerMessage.Text);
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
