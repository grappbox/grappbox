using GrappBox.Model;
using GrappBox.Ressources;
using GrappBox.ViewModel;
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

// Pour en savoir plus sur le modèle d’élément Page vierge, consultez la page http://go.microsoft.com/fwlink/?LinkID=390556

namespace GrappBox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class GenericDahsboard : Page
    {

        Frame frame = Window.Current.Content as Frame;
        public GenericDahsboard()
        {
            this.InitializeComponent();
            this.DataContext = new GenericDashboardViewModel();
        }

        /// <summary>
        /// Invoqué lorsque cette page est sur le point d'être affichée dans un frame.
        /// </summary>
        /// <param name="e">Données d'événement décrivant la manière dont l'utilisateur a accédé à cette page.
        /// Ce paramètre est généralement utilisé pour configurer la page.</param>
        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            GenericDashboardViewModel vmdl = this.DataContext as GenericDashboardViewModel;
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;
            await vmdl.getProjectList();
            LoadingBar.IsEnabled = false;
            LoadingBar.Visibility = Visibility.Collapsed;
        }
        private async void ListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            ListView lv = sender as ListView;
            ProjectListModel plm = lv.SelectedItem as ProjectListModel;
            Debug.WriteLine("ProjectName= {0}  ProjectId= {1}", plm.Name, plm.Id);
            SettingsManager.setOption("ProjectIdChoosen", plm.Id);
            SettingsManager.setOption("ProjectNameChoosen", plm.Name);
            await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => frame.Navigate(typeof(View.DashBoardView)));
        }
    }
}
