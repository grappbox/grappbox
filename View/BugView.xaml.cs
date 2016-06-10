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

        //Required for navigation
        private readonly NavigationHelper navigationHelper;

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
            {
                isAdd = true;
                Comments.Visibility = Visibility.Collapsed;
                Tags.Visibility = Visibility.Collapsed;
                Users.Visibility = Visibility.Collapsed;
            }
            slideInMenuContentControl.MenuState = CustomControler.SlidingMenu.MenuState.Both;
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion

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
            if (vm.State.Id != null && vm.State.Name != null && vm.Title != "" && vm.Description != "")
            {
                if (isAdd == true)
                {
                    vm.addBug();
                    Comments.Visibility = Visibility.Visible;
                    Tags.Visibility = Visibility.Visible;
                    Users.Visibility = Visibility.Visible;
                }
                else
                    vm.editBug();
            }
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
            PostComPopUp.Visibility = Visibility.Collapsed;
            Pivot.IsLocked = false;
            CommentListView.IsEnabled = true;
        }

        private void CancelCom_Click(object sender, RoutedEventArgs e)
        {
            PostComPopUp.Visibility = Visibility.Collapsed;
            Pivot.IsLocked = false;
            CommentListView.IsEnabled = true;
        }
        private void Post_Click(object sender, RoutedEventArgs e)
        {
            PostComPopUp.Visibility = Visibility.Visible;
            Pivot.IsLocked = true;
            CommentListView.IsEnabled = false;
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

        private void checkBox_Click(object sender, RoutedEventArgs e)
        {
            IdNameModel model = (sender as CheckBox).DataContext as IdNameModel;
            bool isInTags = false;

            foreach (var item in vm.Tags)
            {
                if (model.Id == item.Id)
                {
                    vm.removeAssignTag(model);
                    isInTags = true;
                    break;
                }
            }
            if (isInTags == false)
                vm.assignTag(model);
        }
    }
}
