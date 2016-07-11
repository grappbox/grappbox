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
    public sealed partial class Calendar : Page
    {
        //Required for navigation
        private readonly NavigationHelper navigationHelper;
        private CalendarViewModel viewModel
            { get {return (CalendarViewModel)this.DataContext; }
}
        public Calendar()
        {
            this.DataContext = new ViewModel.CalendarViewModel();
            this.InitializeComponent();
            MonthPivot.SelectionChanged += MonthPivot_SelectionChanged;

            //Required for navigation
            this.NavigationCacheMode = NavigationCacheMode.Required;
            this.navigationHelper = new NavigationHelper(this);
            this.navigationHelper.LoadState += this.NavigationHelper_LoadState;
            this.navigationHelper.SaveState += this.NavigationHelper_SaveState;

            MonthPivot.SelectedIndex = viewModel.CurrentMonth - 1;
        }

        private async void MonthPivot_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            Pivot p = sender as Pivot;
            CalendarModel cm = p.SelectedItem as CalendarModel;
            Planning plan = await viewModel.UpdateMonth();
            cm.Events = plan.Events;
            cm.Tasks = plan.Tasks;
            viewModel.NotifyPropertyChanged("MonthList");
            viewModel.NotifyPropertyChanged("Events");
        }

        //Required for navigation
        #region NavigationHelper
        public NavigationHelper NavigationHelper
        {
            get { return this.navigationHelper; }
        }
        private void NavigationHelper_LoadState(object sender, LoadStateEventArgs e)
        {

        }
        private void NavigationHelper_SaveState(object sender, SaveStateEventArgs e)
        {

        }
        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedTo(e);

            CalendarModel cm = viewModel.MonthList[viewModel.CurrentMonth - 1];
            Planning plan = await viewModel.UpdateMonth();
            cm.Events = plan.Events;
            cm.Tasks = plan.Tasks;
            viewModel.NotifyPropertyChanged("MonthList");
            viewModel.NotifyPropertyChanged("Events");
        }

        protected override void OnNavigatedFrom(NavigationEventArgs e)
        {
            this.navigationHelper.OnNavigatedFrom(e);
        }
        #endregion
    }
}
