using Grappbox.Helpers;
using Grappbox.Model;
using Grappbox.ViewModel;
using System;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Threading.Tasks;
using Windows.ApplicationModel.Activation;
using Windows.ApplicationModel.Core;
using Windows.Foundation.Metadata;
using Windows.Storage;
using Windows.Storage.Pickers;
using Windows.Storage.Streams;
using Windows.UI;
using Windows.UI.Core;
using Windows.UI.Popups;
using Windows.UI.ViewManagement;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Grappbox.CustomControls;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace Grappbox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class ProjectSettingsView : Page
    {
        private CoreApplicationView view;
        private String ImagePath;
        private ProjectSettingsViewModel vm = ProjectSettingsViewModel.GetViewModel();
        private DateTime defaultDate = DateTime.MinValue;
        private bool isNew = false;

        public ProjectSettingsView()
        {
            this.InitializeComponent();
            view = CoreApplication.GetCurrentView();
            this.DataContext = vm;
            this.NavigationCacheMode = NavigationCacheMode.Disabled;
        }

        #region NavigationHelper

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
            //Mobile customization
            if (ApiInformation.IsTypePresent("Windows.UI.ViewManagement.StatusBar"))
            {
                var statusBar = StatusBar.GetForCurrentView();
                if (statusBar != null)
                {
                    statusBar.BackgroundOpacity = 1;
                    statusBar.BackgroundColor = (Color)Application.Current.Resources["RedGrappbox"];
                    statusBar.ForegroundColor = (Color)Application.Current.Resources["White1Grappbox"];
                }
            }
            if (e.NavigationMode == NavigationMode.New)
                PivotPS.SelectedIndex = 0;

            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
            dialog.ShowAsync();

            ProjectName.Text = string.Empty;
            ProjectDescription.Text = string.Empty;
            img.Source = null;
            Phone.Text = string.Empty;
            Company.Text = string.Empty;
            Mail.Text = string.Empty;
            Facebook.Text = string.Empty;
            Twitter.Text = string.Empty;
            password.Password = string.Empty;
            oldPassword.Password = string.Empty;
            newPassword.Password = string.Empty;
            retypePassword.Password = string.Empty;
            UserMail.Text = string.Empty;
            CustomerName.Text = string.Empty;
            isNew = false;
            User.IsEnabled = true;
            CustomerAccess.IsEnabled = true;
            Roles.IsEnabled = true;
            DeleteDate.Visibility = Visibility.Collapsed;

            if (e.Parameter == null)
            {
                await vm.getProjectSettings();
                await vm.getProjectUsers();
                await vm.getCustomerAccesses();
                await vm.getRoles();
                await vm.getProjectLogo();

                UpdatePassword.Visibility = Visibility.Visible;
                NewPassword.Visibility = Visibility.Collapsed;

                if (DateTime.Equals(vm.DeletedAt, defaultDate) == false)
                {
                    DeleteDate.Visibility = Visibility.Visible;
                    DeleteDate.Text = string.Format("Your project will be deleted at {0}", vm.DeletedAt.ToString("yyyy-MM-dd hh:mm:ss"));
                }
                else
                {
                    DeleteDate.Visibility = Visibility.Collapsed;
                }
            }
            else
            {
                isNew = true;
                User.IsEnabled = false;
                CustomerAccess.IsEnabled = false;
                Roles.IsEnabled = false;

                vm.UserList = null;
                vm.CustomerList = null;
                vm.RoleList = null;

                UpdatePassword.Visibility = Visibility.Collapsed;
                NewPassword.Visibility = Visibility.Visible;
            }

            dialog.Hide();
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            vm.RoleList = null;
            vm.UserList = null;
            vm.CustomerList = null;
        }

        #endregion NavigationHelper

        #region imgClicked

        private async void Img_Click(object sender, RoutedEventArgs e)
        {
            ImagePath = string.Empty;
            FileOpenPicker filePicker = new FileOpenPicker();
            filePicker.SuggestedStartLocation = PickerLocationId.PicturesLibrary;
            filePicker.ViewMode = PickerViewMode.Thumbnail;

            // Filter to include a sample subset of file types
            filePicker.FileTypeFilter.Clear();
            filePicker.FileTypeFilter.Add(".bmp");
            filePicker.FileTypeFilter.Add(".png");
            filePicker.FileTypeFilter.Add(".jpeg");
            filePicker.FileTypeFilter.Add(".jpg");

            StorageFile file = await filePicker.PickSingleFileAsync();
            if (file != null)
            {
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
                dialog.ShowAsync();

                var stream = await file.OpenAsync(Windows.Storage.FileAccessMode.Read);
                var bitmapImage = new Windows.UI.Xaml.Media.Imaging.BitmapImage();
                await bitmapImage.SetSourceAsync(stream);

                var decoder = await Windows.Graphics.Imaging.BitmapDecoder.CreateAsync(stream);
                img.Source = bitmapImage;

                //For Convert Bitmap Image to Base64
                string newAvatar = await StorageFileToBase64(file);
                vm.logo = newAvatar;

                dialog.Hide();
            }
        }

        private async Task<string> StorageFileToBase64(StorageFile file)
        {
            string Base64String = string.Empty;

            if (file != null)
            {
                IRandomAccessStream fileStream = await file.OpenAsync(FileAccessMode.Read);
                var reader = new DataReader(fileStream.GetInputStreamAt(0));
                await reader.LoadAsync((uint)fileStream.Size);
                byte[] byteArray = new byte[fileStream.Size];
                reader.ReadBytes(byteArray);
                Base64String = Convert.ToBase64String(byteArray);
            }

            return Base64String;
        }

        #endregion imgClicked

        private async void ProjectSettingsUpdate_Click(object sender, RoutedEventArgs e)
        {
            if (isNew == false)
            {
                if (string.IsNullOrEmpty(oldPassword.Password) && string.IsNullOrEmpty(newPassword.Password))
                    await vm.updateProjectSettings();
                else
                {
                    if (newPassword.Password != retypePassword.Password)
                    {
                        newPassword.BorderBrush = new SolidColorBrush(Colors.Red);
                        retypePassword.BorderBrush = new SolidColorBrush(Colors.Red);
                        MessageDialog msgbox = new MessageDialog("New Password and Retype Password must be the same");
                        await msgbox.ShowAsync();
                    }
                    else
                    {
                        await vm.updateProjectSettings(oldPassword.Password, newPassword.Password);
                        newPassword.Password = string.Empty;
                        retypePassword.Password = string.Empty;
                        newPassword.BorderBrush = new SolidColorBrush();
                        retypePassword.BorderBrush = new SolidColorBrush();
                    }
                }
            }
            else
            {
                await vm.createProject(password.Password);
                if (SettingsManager.getOption("ProjectIdChoosen") != -1)
                    Frame.GoBack();
            }
        }

        private async void AddUser_Click(object sender, RoutedEventArgs e)
        {
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
            dialog.ShowAsync();

            await vm.addProjectUser(UserMail.Text);
            UserMail.Text = string.Empty;

            dialog.Hide();
        }

        private async void RemoveUserButton_Click(object sender, RoutedEventArgs e)
        {
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
            dialog.ShowAsync();

            await vm.removeProjectUser();

            dialog.Hide();
        }

        private async void Delete_Click(object sender, RoutedEventArgs e)
        {
            if (DateTime.Equals(vm.DeletedAt, defaultDate) == false)
            {
                //retreive
                await vm.retrieveProject();
                await vm.getProjectSettings();
                if (vm.DeletedAt == null)
                {
                    DeleteDate.Visibility = Visibility.Collapsed;
                    ProjectDelete.Label = "Delete Project";
                }
            }
            else
            {
                //delete
                await vm.deleteProject();
                await vm.getProjectSettings();
                if (vm.DeletedAt != null)
                {
                    DeleteDate.Visibility = Visibility.Visible;
                    DeleteDate.Text = string.Format("Your project will be deleted at {0}", vm.DeletedAt.ToString("yyyy-MM-dd hh:mm:ss"));
                    ProjectDelete.Label = "Retreive Project";
                }
            }
        }

        private void userListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.UserSelected = (sender as ListView).SelectedItem as UsersModel;
        }

        private void Pivot_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            int num = PivotPS.SelectedIndex;

            if (num == 0)
            {
                CB.Visibility = Visibility.Visible;
                ProjectDelete.Visibility = Visibility.Visible;
                UpdateSettings.Visibility = Visibility.Visible;
                RemoveUser.Visibility = Visibility.Collapsed;
                RemoveCU.Visibility = Visibility.Collapsed;
                RegenerateCu.Visibility = Visibility.Collapsed;
                RemoveRole.Visibility = Visibility.Collapsed;
                ModifyRole.Visibility = Visibility.Collapsed;
            }
            else if (num == 1)
            {
                CB.Visibility = Visibility.Visible;
                ProjectDelete.Visibility = Visibility.Collapsed;
                UpdateSettings.Visibility = Visibility.Collapsed;
                RemoveUser.Visibility = Visibility.Visible;
                RemoveCU.Visibility = Visibility.Collapsed;
                RegenerateCu.Visibility = Visibility.Collapsed;
                RemoveRole.Visibility = Visibility.Collapsed;
                ModifyRole.Visibility = Visibility.Collapsed;
            }
            else if (num == 2)
            {
                CB.Visibility = Visibility.Visible;
                ProjectDelete.Visibility = Visibility.Collapsed;
                UpdateSettings.Visibility = Visibility.Collapsed;
                RemoveUser.Visibility = Visibility.Collapsed;
                RemoveCU.Visibility = Visibility.Visible;
                RegenerateCu.Visibility = Visibility.Visible;
                RemoveRole.Visibility = Visibility.Collapsed;
                ModifyRole.Visibility = Visibility.Collapsed;
            }
            else if (num == 3)
            {
                CB.Visibility = Visibility.Visible;
                ProjectDelete.Visibility = Visibility.Collapsed;
                UpdateSettings.Visibility = Visibility.Collapsed;
                RemoveUser.Visibility = Visibility.Collapsed;
                RemoveCU.Visibility = Visibility.Collapsed;
                RegenerateCu.Visibility = Visibility.Collapsed;
                RemoveRole.Visibility = Visibility.Visible;
                ModifyRole.Visibility = Visibility.Visible;
            }
            else
            {
                CB.Visibility = Visibility.Collapsed;
            }
        }

        private async void RegenerateCU_Click(object sender, RoutedEventArgs e)
        {
            await vm.regenerateCustomerAccess();
        }

        private async void RemoveCustomerButton_Click(object sender, RoutedEventArgs e)
        {
            await vm.removeCustomerAccess();
        }

        private async void AddCU_Click(object sender, RoutedEventArgs e)
        {
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
            dialog.ShowAsync();

            ObservableCollection<CustomerAccessModel> cuList = vm.CustomerList;
            bool exist = false;

            foreach (var item in cuList)
            {
                if (item.Name == CustomerName.Text)
                    exist = true;
            }
            if (!string.IsNullOrEmpty(CustomerName.Text) && exist == false)
            {
                await vm.addCustomerAccess(CustomerName.Text);
                CustomerName.Text = string.Empty;
            }
            else
            {
                if (string.IsNullOrEmpty(CustomerName.Text))
                {
                    MessageDialog msgbox = new MessageDialog("The name must not be empty");
                    await msgbox.ShowAsync();
                }
                else
                {
                    MessageDialog msgbox = new MessageDialog("The name must be different from an existing one");
                    await msgbox.ShowAsync();
                }
            }

            dialog.Hide();
        }

        private void customerListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.CustomerSelected = (sender as ListView).SelectedItem as CustomerAccessModel;
        }

        private async void AddRole_Click(object sender, RoutedEventArgs e)
        {
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(View.RoleView), null));
        }

        private void roleListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.RoleSelected = (sender as ListView).SelectedItem as ProjectRoleModel;
        }

        private async void ModifyRole_Click(object sender, RoutedEventArgs e)
        {
            if (vm.RoleSelected != null)
            {
                await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(RoleView), vm.RoleSelected));
            }
        }

        private async void RemoveRoleButton_Click(object sender, RoutedEventArgs e)
        {
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
            dialog.ShowAsync();

            if (vm.RoleSelected != null)
                await vm.removeRole();

            dialog.Hide();
        }

        private async void ComboBox_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            var parent = (sender as ComboBox).Parent;
            var value = (sender as ComboBox).SelectedValue;
            if (parent != null && value != null)
            {
                UserModel md = (parent as Grid).DataContext as UserModel;
                ProjectRoleModel role = await vm.getUserRole(((parent as Grid).DataContext as UserModel).Id);
                int newRole = (int)value;
                bool success = false;
                if (role.RoleId != newRole)
                {
                    if (role.RoleId == 0 || await vm.removeUserRole(md.Id, role.RoleId) == true)
                        success = await vm.assignUserRole(md.Id, newRole);
                }
                if (success == false)
                    (sender as ComboBox).SelectedValue = role.RoleId;
            }
        }

        private async void ComboBox_Loaded(object sender, RoutedEventArgs e)
        {
            UserModel md = (sender as ComboBox).DataContext as UserModel;
            ProjectRoleModel role = await vm.getUserRole(md.Id);
            if (role != null)
                (sender as ComboBox).SelectedValue = role.RoleId;
        }

        private void TextBox_GotFocus(object sender, RoutedEventArgs e)
        {
            CB.Visibility = Visibility.Collapsed;
        }

        private void TextBox_LostFocus(object sender, RoutedEventArgs e)
        {
            CB.Visibility = Visibility.Visible;
        }
    }
}