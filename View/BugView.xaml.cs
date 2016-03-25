using GrappBox.ApiCom;
using GrappBox.Model;
using GrappBox.ViewModel;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
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
    public sealed partial class BugView : Page
    {
        BugtrackerViewModel vm = BugtrackerViewModel.GetViewModel();
        bool isAdd;

        static private BugView instance = null;
        static public BugView GetInstance()
        {
            return instance;
        }
        public BugView()
        {
            this.InitializeComponent();
            this.DataContext = vm;
            instance = this;
        }

        /// <summary>
        /// Invoked when this page is about to be displayed in a Frame.
        /// </summary>
        /// <param name="e">Event data that describes how this page was reached.
        /// This parameter is typically used to configure the page.</param>
        protected override void OnNavigatedTo(NavigationEventArgs e)
        {
            if (e.Parameter != null)
            {
                isAdd = false;
                if (vm.creator != null && vm.creator.Id != User.GetUser().Id)
                {
                    Title.IsEnabled = false;
                    Description.IsEnabled = false;
                    StatesListView.IsEnabled = false;
                    SaveBug.Visibility = Visibility.Collapsed;
                }
            }
            else
                isAdd = true;
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
        #endregion menuClicked

        private void Pivot_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            int num = Pivot.SelectedIndex;

            if (num == 0)
            {
                SaveBug.Visibility = Visibility.Visible;
                PostComment.Visibility = Visibility.Collapsed;
                RemoveTag.Visibility = Visibility.Collapsed;
                EditTag.Visibility = Visibility.Collapsed;
                SaveUser.Visibility = Visibility.Collapsed;
            }
            else if (num == 1)
            {
                SaveBug.Visibility = Visibility.Collapsed;
                PostComment.Visibility = Visibility.Visible;
                RemoveTag.Visibility = Visibility.Collapsed;
                EditTag.Visibility = Visibility.Collapsed;
                SaveUser.Visibility = Visibility.Collapsed;
            }
            else if (num == 2)
            {
                SaveBug.Visibility = Visibility.Collapsed;
                PostComment.Visibility = Visibility.Collapsed;
                RemoveTag.Visibility = Visibility.Visible;
                EditTag.Visibility = Visibility.Visible;
                SaveUser.Visibility = Visibility.Collapsed;
            }
            else if (num == 3)
            {
                SaveBug.Visibility = Visibility.Collapsed;
                PostComment.Visibility = Visibility.Collapsed;
                RemoveTag.Visibility = Visibility.Collapsed;
                EditTag.Visibility = Visibility.Collapsed;
                SaveUser.Visibility = Visibility.Visible;
            }
        }

        #region Bug
        private void SaveBug_Click(object sender, RoutedEventArgs e)
        {
            if (isAdd == true)
                vm.addBug();
            else
                vm.editBug();
        }

        private void radioButton_Loaded(object sender, RoutedEventArgs e)
        {
            if (vm.State != null && (sender as RadioButton).Content != null && (sender as RadioButton).Content.ToString() == vm.State.Name)
                (sender as RadioButton).IsChecked = true;
        }

        private void StatesListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            (sender as ListBox).SelectedItem = null;
        }

        private void RadioButton_Checked(object sender, RoutedEventArgs e)
        {
            foreach (var item in vm.StateList)
            {
                if (item.Name == (sender as RadioButton).Content.ToString())
                {
                    vm.State.Id = item.Id;
                    vm.State.Name = item.Name;
                    break;
                }
            }
        }
        #endregion

        #region Tag
        private void AddTag_Click(object sender, RoutedEventArgs e)
        {
            if (TagName.Text != "")
                vm.addTag(TagName.Text);
        }

        private void checkBox_Loaded(object sender, RoutedEventArgs e)
        {
            if (vm.Tags != null)
            {
                UIElementCollection children = ((sender as CheckBox).Parent as StackPanel).Children;
                string content = "";

                foreach (var item in children)
                {
                    if ((item as TextBox) != null && (item as TextBox).Name == "BoxName")
                    {
                        content = (item as TextBox).Text;
                    }

                }
                foreach (var item in vm.Tags)
                {
                    if (content == item.Name)
                    {
                        (sender as CheckBox).IsChecked = true;
                    }
                }
            }
        }
        private void checkBox_Checked(object sender, RoutedEventArgs e)
        {
            IdNameModel model = (sender as CheckBox).DataContext as IdNameModel;

            vm.assignTag(model);
        }

        private void checkBox_Unchecked(object sender, RoutedEventArgs e)
        {
            IdNameModel model = (sender as CheckBox).DataContext as IdNameModel;

            vm.removeAssignTag(model);
        }

        private void UpdateTag_Click(object sender, RoutedEventArgs e)
        {
            if (vm.TagSelect != null)
                vm.editTag();
        }
        private void RemoveTag_Click(object sender, RoutedEventArgs e)
        {
            if (vm.TagSelect != null)
                vm.deleteTag();
        }

        private void tagListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.TagSelect = (sender as ListBox).SelectedItem as IdNameModel;
        }
        #endregion

        #region User
        private void userCheckBox_Checked(object sender, RoutedEventArgs e)
        {
            ProjectUserModel model = (sender as CheckBox).DataContext as ProjectUserModel;

            vm.ToAdd.Add(model.Id);
            vm.ToRemove.Remove(model.Id);
        }

        private void userCheckBox_Loaded(object sender, RoutedEventArgs e)
        {
            if (vm.Users != null)
            {
                UIElementCollection children = ((sender as CheckBox).Parent as StackPanel).Children;
                string content = "";

                foreach (var item in children)
                {
                    if ((item as TextBlock) != null && ((item as TextBlock).Name == "firstname" || (item as TextBlock).Name == "lastname"))
                    {
                        content += (item as TextBlock).Text;
                        if ((item as TextBlock).Name == "firstname")
                            content += " ";
                    }

                }
                foreach (var item in vm.Users)
                {
                    if (content == item.Name)
                    {
                        (sender as CheckBox).IsChecked = true;
                    }
                }
            }
        }

        private void userCheckBox_Unchecked(object sender, RoutedEventArgs e)
        {
            ProjectUserModel model = (sender as CheckBox).DataContext as ProjectUserModel;

            vm.ToAdd.Remove(model.Id);
            vm.ToRemove.Add(model.Id);
        }
        private void SaveUser_Click(object sender, RoutedEventArgs e)
        {
            vm.setParticipants();
        }
        #endregion

        #region Comment
        private void EditComment_Click(object sender, RoutedEventArgs e)
        {
            vm.editComment((sender as Button).DataContext as BugtrackerModel);
        }

        private void DeleteComment_Click(object sender, RoutedEventArgs e)
        {
            vm.deleteComment((sender as Button).DataContext as BugtrackerModel);
        }

        private void PostComment_Click(object sender, RoutedEventArgs e)
        {
            vm.addComment(CommentTitle.Text, CommentDescription.Text);
        }

        private void StackPanel_Loaded(object sender, RoutedEventArgs e)
        {
            BugtrackerModel currentModel = (sender as StackPanel).DataContext as BugtrackerModel;

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
        #endregion
    }
}
