﻿using GrappBox.Model;
using GrappBox.Resources;
using GrappBox.ViewModel;
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

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
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

            //Required for navigation
            this.NavigationCacheMode = NavigationCacheMode.Required;
        }

        //Required for navigation

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

            await filePicker.PickSingleFileAsync();
            view.Activated += viewActivated;
        }

        private async void viewActivated(CoreApplicationView sender, IActivatedEventArgs args1)
        {
            FileOpenPickerContinuationEventArgs args = args1 as FileOpenPickerContinuationEventArgs;

            if (args != null)
            {
                if (args.Files.Count == 0) return;

                view.Activated -= viewActivated;
                StorageFile storageFile = args.Files[0];
                var stream = await storageFile.OpenAsync(Windows.Storage.FileAccessMode.Read);
                var bitmapImage = new Windows.UI.Xaml.Media.Imaging.BitmapImage();
                await bitmapImage.SetSourceAsync(stream);

                var decoder = await Windows.Graphics.Imaging.BitmapDecoder.CreateAsync(stream);
                img.Source = bitmapImage;

                //For Convert Bitmap Image to Base64
                string newAvatar = await StorageFileToBase64(args.Files[0]);
                vm.logo = newAvatar;
            }
        }

        private async Task<string> StorageFileToBase64(StorageFile file)
        {
            string Base64String = "";

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
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            if (isNew == false)
            {
                if (oldPassword.Password == "" && newPassword.Password == "")
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
                        newPassword.Password = "";
                        retypePassword.Password = "";
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

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        private async void AddUser_Click(object sender, RoutedEventArgs e)
        {
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            await vm.addProjectUser(UserMail.Text);
            UserMail.Text = "";

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        private async void RemoveUserButton_Click(object sender, RoutedEventArgs e)
        {
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            await vm.removeProjectUser();

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        private async void Delete_Click(object sender, RoutedEventArgs e)
        {
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

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
                    DeleteDate.Text = "Your project will be deleted at " + vm.DeletedAt.ToString("yyyy-MM-dd hh:mm:ss");
                    ProjectDelete.Label = "Retreive Project";
                }
            }

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        private void userListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            vm.UserSelected = (sender as ListView).SelectedItem as UserModel;
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
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            await vm.regenerateCustomerAccess();

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        private async void RemoveCustomerButton_Click(object sender, RoutedEventArgs e)
        {
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            await vm.removeCustomerAccess();

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }

        private async void AddCU_Click(object sender, RoutedEventArgs e)
        {
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            ObservableCollection<CustomerAccessModel> cuList = vm.CustomerList;
            bool exist = false;

            foreach (var item in cuList)
            {
                if (item.Name == CustomerName.Text)
                    exist = true;
            }
            if (CustomerName.Text != "" && CustomerName.Text != null && exist == false)
            {
                await vm.addCustomerAccess(CustomerName.Text);
                CustomerName.Text = "";
            }
            else
            {
                if (CustomerName.Text == "" && CustomerName.Text == null)
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

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
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
                if (vm.RoleSelected != null)
                {
                    await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(RoleView), vm.RoleSelected));
                }
            }
        }

        private async void RemoveRoleButton_Click(object sender, RoutedEventArgs e)
        {
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            if (vm.RoleSelected != null)
                await vm.removeRole();

            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
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
                if (role.RoleId != newRole)
                {
                    if (role.RoleId == 0 || await vm.removeUserRole(md.Id, role.RoleId) == true)
                        await vm.assignUserRole(md.Id, newRole);
                }
            }
        }

        private async void ComboBox_Loaded(object sender, RoutedEventArgs e)
        {
            UserModel md = (sender as ComboBox).DataContext as UserModel;
            ProjectRoleModel role = await vm.getUserRole(md.Id);
            if (role != null)
                (sender as ComboBox).SelectedValue = role.RoleId;
        }
    }
}