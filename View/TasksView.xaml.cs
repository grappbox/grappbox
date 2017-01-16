using System;
using System.Collections.Generic;
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
using Grappbox.ViewModel;
using Windows.Foundation.Metadata;
using Windows.UI.ViewManagement;
using Windows.UI;
using Grappbox.CustomControls;
using Grappbox.Helpers;
using System.Diagnostics;
using Grappbox.Model;

// Pour plus d'informations sur le modèle d'élément Page vierge, voir la page http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class TasksView : Page
    {
        public TasksView()
        {
            this.InitializeComponent();
        }

        protected override async void OnNavigatedTo(NavigationEventArgs e)
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
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("BlueGrappboxBrush"));
            dialog.ShowAsync();
            await viewModel.GetTasks();
            viewModel.NotifyPropertyChanged("FilteredTaskList");
            dialog.Hide();
            Debug.WriteLine("Tasks: {0}", viewModel.TaskList.Count);
            Debug.WriteLine("Items: {0}", TaskListView.Items.Count);
        }

        private void AddTask_Click(object sender, RoutedEventArgs e)
        {
            this.Frame.Navigate(typeof(TaskDetailView), null);
        }

        private void AllFilter_Click(object sender, RoutedEventArgs e)
        {
            viewModel.TaskListFilter = "All";
            viewModel.NotifyPropertyChanged("FilteredTaskList");
        }

        private void StartedFilter_Click(object sender, RoutedEventArgs e)
        {
            viewModel.TaskListFilter = "Started";
            viewModel.NotifyPropertyChanged("FilteredTaskList");
        }

        private void FinishedFilter_Click(object sender, RoutedEventArgs e)
        {
            viewModel.TaskListFilter = "Finished";
            viewModel.NotifyPropertyChanged("FilteredTaskList");
        }

        private void TaskListView_ItemClick(object sender, ItemClickEventArgs e)
        {
            var t = e.ClickedItem as TaskModel;
            this.Frame.Navigate(typeof(TaskDetailView), t);
        }
    }
}