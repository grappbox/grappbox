using GrappBox.ViewModel;
using System;
using Windows.ApplicationModel.Activation;
using Windows.ApplicationModel.Core;
using Windows.Storage;
using Windows.Storage.Pickers;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Windows.UI;
using GrappBox.Ressources;
using System.Threading.Tasks;
using Windows.Storage.Streams;
using GrappBox.Model;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class UserView : Page
    {
        CoreApplicationView view;
        String ImagePath;
        bool isPasswordVisible = false;
        string password = "";

        static private UserView instance = null;
        static public UserView GetUser()
        {
            return instance;
        }

        public UserView()
        {
            instance = this;
            this.InitializeComponent();
            view = CoreApplication.GetCurrentView();
            this.DataContext = UserSettingsViewModel.GetUserSettingsViewModel();
        }

        /// <summary>
        /// Invoked when this page is about to be displayed in a Frame.
        /// </summary>
        /// <param name="e">Event data that describes how this page was reached.
        /// This parameter is typically used to configure the page.</param>
        protected override void OnNavigatedTo(NavigationEventArgs e)
        {
        }

        private void Dashboard_Click(object sender, RoutedEventArgs e)
        {
            this.Frame.Navigate(typeof(DashBoardView));
        }

        private void Whiteboard_Click(object sender, RoutedEventArgs e)
        {
            this.Frame.Navigate(typeof(WhiteBoardView));
        }

        private void UserSettings_Click(object sender, RoutedEventArgs e)
        {
            this.Frame.Navigate(typeof(UserView));
        }

        private void Update_Click(object sender, RoutedEventArgs e)
        {
            if (password != "")
            {
                UserSettingsViewModel.GetUserSettingsViewModel().updateAPI(password);
                password = "";
            }
            else
                UserSettingsViewModel.GetUserSettingsViewModel().updateAPI();
        }

        private void Password_Click(object sender, RoutedEventArgs e)
        {
            if (isPasswordVisible == false)
            {
                PasswordStackPanel.Visibility = Visibility.Visible;
                isPasswordVisible = true;
            }
            else
            {
                string oldpwd = SettingsManager.getOption<string>("password");
                if (newPassword.Password == retypePassword.Password && oldpwd == oldPassword.Password && newPassword.Password != oldpwd && newPassword.Password.Length > 0)
                {
                    oldPassword.BorderBrush = new SolidColorBrush();
                    newPassword.BorderBrush = new SolidColorBrush();
                    retypePassword.BorderBrush = new SolidColorBrush();
                    PasswordStackPanel.Visibility = Visibility.Collapsed;
                    password = newPassword.Password;
                    isPasswordVisible = false;
                    affMessage(false, "Password will be changed on update");
                }
                if (oldpwd != oldPassword.Password)
                {
                    oldPassword.BorderBrush = new SolidColorBrush(Colors.Red);
                    affMessage(true, "The old password doesn't match");
                }
                else
                    oldPassword.BorderBrush = new SolidColorBrush();
                if (newPassword.Password != retypePassword.Password)
                {
                    newPassword.BorderBrush = new SolidColorBrush(Colors.Red);
                    retypePassword.BorderBrush = new SolidColorBrush(Colors.Red);
                    affMessage(true, "New Password and Retype Password must be the same");
                }
                else
                {
                    newPassword.BorderBrush = new SolidColorBrush();
                    retypePassword.BorderBrush = new SolidColorBrush();
                }
                if (oldpwd == newPassword.Password)
                {
                    oldPassword.BorderBrush = new SolidColorBrush(Colors.Red);
                    newPassword.BorderBrush = new SolidColorBrush(Colors.Red);
                    affMessage(true, "The old password can't be the same as the new one");
                }
                if (newPassword.Password.Length == 0)
                {
                    newPassword.BorderBrush = new SolidColorBrush(Colors.Red);
                    retypePassword.BorderBrush = new SolidColorBrush(Colors.Red);
                    affMessage(true, "Your password can't be empty");
                }
                else
                {
                    newPassword.BorderBrush = new SolidColorBrush();
                    retypePassword.BorderBrush = new SolidColorBrush();
                }
            }
        }

        private void Img_Click(object sender, RoutedEventArgs e)
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

            filePicker.PickSingleFileAndContinue();
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
                UserSettingsViewModel.GetUserSettingsViewModel().avatar = newAvatar;
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

        public void affMessage(bool isError, string message)
        {
            if (isError == true)
            {
                infoBlock.Foreground = new SolidColorBrush(Colors.Red);
                infoBlock.Text = message;
            }
            else
            {
                infoBlock.Foreground = new SolidColorBrush(Colors.Green);
                infoBlock.Text = message;
            }
        }
    }
}
