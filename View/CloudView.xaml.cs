using Grappbox.CustomControls;
using Grappbox.Helpers;
using Grappbox.Model;
using Grappbox.ViewModel;
using System;
using System.Threading.Tasks;
using Windows.ApplicationModel.Core;
using Windows.Foundation.Metadata;
using Windows.Storage;
using Windows.Storage.Pickers;
using Windows.Storage.Streams;
using Windows.UI;
using Windows.UI.ViewManagement;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace Grappbox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class CloudView : Page
    {
        TimelineViewModel tvm = new TimelineViewModel();
        CoreApplicationView view;
        CloudViewModel vm = CloudViewModel.GetViewModel();

        /// <summary>
        /// Initializes a new instance of the <see cref="CloudView"/> class.
        /// </summary>
        public CloudView()
        {
            this.InitializeComponent();
            view = CoreApplication.GetCurrentView();
            this.DataContext = vm;

            //Required for navigation
            this.NavigationCacheMode = NavigationCacheMode.Required;
        }

        //Required for navigation
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
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("YellowGrappboxBrush"));
            dialog.ShowAsync();

            //Mobile customization
            if (ApiInformation.IsTypePresent("Windows.UI.ViewManagement.StatusBar"))
            {
                var statusBar = StatusBar.GetForCurrentView();
                if (statusBar != null)
                {
                    statusBar.BackgroundOpacity = 1;
                    statusBar.BackgroundColor = (Color)Application.Current.Resources["YellowGrappbox"];
                    statusBar.ForegroundColor = (Color)Application.Current.Resources["White1Grappbox"];
                }
            }
            await vm.getLS();

            PH.HeaderContent = "Cloud";

            dialog.Hide();
        }

        /// <summary>
        /// Invoked immediately after the Page is unloaded and is no longer the current source of a parent Frame.
        /// </summary>
        /// <param name="e">Event data that can be examined by overriding code. The event data is representative of the navigation that has unloaded the current Page.</param>
        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
        }
        #endregion

        /// <summary>
        /// Handles the Click event of the AddFolder control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void AddFolder_Click(object sender, RoutedEventArgs e)
        {
            CloudFolder dialog = new CloudFolder();
            await dialog.ShowAsync();
        }

        /// <summary>
        /// Handles the Click event of the UploadFile control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void UploadFile_Click(object sender, RoutedEventArgs e)
        {
            FileOpenPicker openPicker = new FileOpenPicker();
            openPicker.FileTypeFilter.Add("*");
            openPicker.ViewMode = PickerViewMode.Thumbnail;
            openPicker.SuggestedStartLocation = PickerLocationId.DocumentsLibrary;

            StorageFile file = await openPicker.PickSingleFileAsync();

            if (file != null)
            {                
                vm.FileData = await StorageFileToBase64(file);
                await vm.uploadFile(file.Name);
            }
        }

        /// <summary>
        /// Storages the file to base64.
        /// </summary>
        /// <param name="file">The file.</param>
        /// <returns></returns>
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

        /// <summary>
        /// Handles the DownloadClick event of the MenuFlyoutItem control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void MenuFlyoutItem_DownloadClick(object sender, RoutedEventArgs e)
        {
            if (vm.FileSelect.IsSecured)
            {
                CloudPassword dialogpwd = new CloudPassword();
                await dialogpwd.ShowAsync();
            }

            vm.FullPath.Add(vm.FileSelect.Filename);
            await vm.downloadFile();
        }

        /// <summary>
        /// Handles the DeleteClick event of the MenuFlyoutItem control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void MenuFlyoutItem_DeleteClick(object sender, RoutedEventArgs e)
        {
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("YellowGrappboxBrush"));
            dialog.ShowAsync();

            vm.FullPath.Add(vm.FileSelect.Filename);
            await vm.deleteFile();

            dialog.Hide();
        }

        /// <summary>
        /// Handles the Click event of the PreviousFolder control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void PreviousFolder_Click(object sender, RoutedEventArgs e)
        {
            if (vm.FullPath.Count > 0)
            {
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("YellowGrappboxBrush"));
                dialog.ShowAsync();

                vm.FullPath.RemoveAt(vm.FullPath.Count - 1);
                await vm.getLS();

                if (vm.FullPath.Count > 1)
                    PH.HeaderContent = vm.FullPath[vm.FullPath.Count - 1];
                else
                    PH.HeaderContent = "Cloud";

                dialog.Hide();
            }
        }

        /// <summary>
        /// Handles the Click event of the Home control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void Home_Click(object sender, RoutedEventArgs e)
        {
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("YellowGrappboxBrush"));
            dialog.ShowAsync();

            vm.FullPath.Clear();
            await vm.getLS();
            PH.HeaderContent = "Cloud";

            dialog.Hide();
        }

        /// <summary>
        /// Handles the Holding event of the Grid control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="HoldingRoutedEventArgs"/> instance containing the event data.</param>
        private void Grid_Holding(object sender, HoldingRoutedEventArgs e)
        {
            FrameworkElement senderElement = sender as FrameworkElement;

            FlyoutBase flyoutBase = FlyoutBase.GetAttachedFlyout(senderElement);
            flyoutBase.ShowAt(senderElement);
            vm.FileSelect = (sender as Grid).DataContext as CloudModel;
        }

        /// <summary>
        /// Handles the SelectionChanged event of the listView control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="TappedRoutedEventArgs"/> instance containing the event data.</param>
        private async void listView_SelectionChanged(object sender, TappedRoutedEventArgs e)
        {
            vm.FileSelect = (sender as Grid).DataContext as CloudModel;
            if (vm.FileSelect != null)
            {
                if (vm.FileSelect.Filename == "Safe" && string.IsNullOrEmpty(vm.PasswordSafe))
                {
                    CloudPasswordSafe dialogpwd = new CloudPasswordSafe();
                    await dialogpwd.ShowAsync();
                }
                var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("YellowGrappboxBrush"));
                dialog.ShowAsync();

                vm.FullPath.Add(vm.FileSelect.Filename);
                if (vm.FileSelect.Type == "dir")
                {
                    PH.HeaderContent = vm.FileSelect.Filename;
                    await vm.getLS();
                    vm.FileSelect = null;
                }
                dialog.Hide();
            }
        }

        /// <summary>
        /// Handles the InfoClick event of the MenuFlyoutItem control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void MenuFlyoutItem_InfoClick(object sender, RoutedEventArgs e)
        {
            CloudInfos dialoginfo = new CloudInfos();
            await dialoginfo.ShowAsync();
        }
    }
}
