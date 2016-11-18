﻿using Grappbox.CustomControls;
using Grappbox.Helpers;
using Grappbox.Model;
using Grappbox.ViewModel;
using System;
using System.Diagnostics;
using Windows.UI.Core;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;

namespace Grappbox.View
{
    public sealed partial class GenericDahsboard : Page
    {
        public GenericDahsboard()
        {
            this.InitializeComponent();
            this.DataContext = new GenericDashboardViewModel();
        }

        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            GenericDashboardViewModel vmdl = this.DataContext as GenericDashboardViewModel;
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
            dialog.ShowAsync();
            await vmdl.getProjectList();
            await vmdl.getProjectsLogo();
            dialog.Hide();
        }

        private void ListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            ListView lv = sender as ListView;
            ProjectListModel plm = lv.SelectedItem as ProjectListModel;
            AppGlobalHelper.ProjectId = plm.Id;
            AppGlobalHelper.ProjectName = plm.Name;
            Debug.WriteLine("ProjectId= {0}", plm.Id);
            Frame.Navigate(typeof(View.DashBoardView));
        }

        private void CreateProject_Click(object sender, RoutedEventArgs e)
        {
            Frame.Navigate(typeof(View.ProjectSettingsView), "newProject");
        }
    }
}