using GrappBox.ViewModel;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
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

// Pour plus d'informations sur le modèle d'élément Page vierge, voir la page http://go.microsoft.com/fwlink/?LinkId=234238

namespace GrappBox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class CalendarView : Page
    {
        public CalendarViewModel ViewModel { get; private set; }

        public CalendarView()
        {
            this.InitializeComponent();
            ViewModel = new CalendarViewModel();
            this.DataContext = ViewModel;
        }

        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            //Mobile customization
            if (ApiInformation.IsTypePresent("Windows.UI.ViewManagement.StatusBar"))
            {
                var statusBar = StatusBar.GetForCurrentView();
                if (statusBar != null)
                {
                    statusBar.BackgroundOpacity = 1;
                    statusBar.BackgroundColor = (Color)Application.Current.Resources["BlueGrappbox"];
                    statusBar.ForegroundColor = (Color)Application.Current.Resources["White1Grappbox"];
                }
            }
            base.OnNavigatedTo(e);
            ProgressRing.Visibility = Visibility.Visible;
            await ViewModel.GetDay(DateTime.Today);
            ProgressRing.Visibility = Visibility.Collapsed;
        }

        private async void CalendarView_SelectedDatesChanged(Windows.UI.Xaml.Controls.CalendarView sender, CalendarViewSelectedDatesChangedEventArgs args)
        {
            DateTimeOffset selectedDate = args.AddedDates[0];
            ProgressRing.Visibility = Visibility.Visible;
            await this.ViewModel.GetDay(selectedDate.Date);
            ProgressRing.Visibility = Visibility.Collapsed;
        }
    }
}