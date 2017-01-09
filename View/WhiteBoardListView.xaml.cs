using Grappbox.Model;
using Grappbox.ViewModel;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI.Core;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Grappbox.CustomControls.Whiteboard;
using Grappbox.CustomControls;
using Grappbox.Helpers;
using Windows.UI.ViewManagement;
using Windows.UI;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkID=390556

namespace Grappbox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class WhiteBoardListView : Page
    {
        public WhiteBoardListView()
        {
            this.InitializeComponent();
        }
        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            StatusBar.GetForCurrentView().BackgroundColor = (Color)SystemInformation.GetStaticResource("GreenGrappbox");
            await viewModel.GetWhiteboards();
            whiteboardList.ItemsSource = viewModel.Whiteboards;
        }

        private async void CreateWhiteBoard(object sender, RoutedEventArgs e)
        {
            NewWhiteboard dialog = new NewWhiteboard();
            await dialog.ShowAsync();
            if (dialog.Result == ContentDialogResult.Primary)
            {
                LoaderDialog loader = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("GreenGrappboxBrush"));
                loader.ShowAsync();
                await viewModel.CreateWhiteboard(dialog.WhiteBoardName);
                loader.Hide();
            }
        }

        private void ListView_ItemClick(object sender, ItemClickEventArgs e)
        {
            WhiteBoardListModel wblm = e.ClickedItem as WhiteBoardListModel;
            Debug.WriteLine(wblm.Name);
            this.Frame.Navigate(typeof(WhiteBoardView), wblm.Id);
        }
    }
}
