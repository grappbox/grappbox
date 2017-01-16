using Grappbox.CustomControls;
using Grappbox.ViewModel;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.ApplicationModel.Core;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.Foundation.Metadata;
using Windows.UI;
using Windows.UI.ViewManagement;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using WinRTXamlToolkit.Controls.DataVisualization.Charting;

// The Blank Page item template is documented at http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.View
{
    /// <summary>
    /// An empty page that can be used on its own or navigated to within a Frame.
    /// </summary>
    public sealed partial class StatsView : Page
    {
        CoreApplicationView view;
        StatsViewModel vm = StatsViewModel.GetViewModel();

        /// <summary>
        /// Initializes a new instance of the <see cref="StatsView"/> class.
        /// </summary>
        public StatsView()
        {
            this.InitializeComponent();
            view = CoreApplication.GetCurrentView();
            this.DataContext = vm;
            this.NavigationCacheMode = NavigationCacheMode.Required;
        }


        #region NavigationHelper        
        /// <summary>
        /// Invoked when the Page is loaded and becomes the current source of a parent Frame.
        /// </summary>
        /// <param name="e">Event data that can be examined by overriding code. The event data is representative of the pending navigation that will load the current Page. Usually the most relevant property to examine is Parameter.</param>
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
            var dialog = new LoaderDialog(Helpers.SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
            dialog.ShowAsync();

            await vm.getAllStats();

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
    }
}
