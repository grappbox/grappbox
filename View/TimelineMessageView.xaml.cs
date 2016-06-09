using GrappBox.ApiCom;
using GrappBox.Model;
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
        }

        /// <summary>
        /// Invoked when this page is about to be displayed in a Frame.
        /// </summary>
        /// <param name="e">Event data that describes how this page was reached.
        /// This parameter is typically used to configure the page.</param>
        protected override void OnNavigatedTo(NavigationEventArgs e)
        {
        }

        private void StackPanel_Loaded(object sender, RoutedEventArgs e)
        {
            TimelineModel currentModel = (sender as StackPanel).DataContext as TimelineModel;

            if (currentModel == null)
                currentModel = vm.MessageSelected;

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

        private void CommentsListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.CommentSelected = (sender as ListBox).SelectedItem as TimelineModel;
        }

        private void EditMessage_Click(object sender, RoutedEventArgs e)
        {
            vm.CommentSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.CommentSelected != null)
                vm.updateMessage(vm.CommentSelected);
        }

        private void DeleteMessage_Click(object sender, RoutedEventArgs e)
        {
            vm.CommentSelected = (sender as Button).DataContext as TimelineModel;
            if (vm.CommentSelected != null)
                vm.removeMessage(vm.CommentSelected);
        }

        private void PostComment_Click(object sender, RoutedEventArgs e)
        {
            if (CommentTitle.Text != "" && CommentMessage.Text != "")
            {
                vm.postMessage(vm.MessageSelected.TimelineId, CommentTitle.Text, CommentMessage.Text, vm.MessageSelected.Id);
                CommentTitle.Text = "";
                CommentMessage.Text = "";
                PostComPopUp.Visibility = Visibility.Collapsed;
                CommentsListView.IsEnabled = true;
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
