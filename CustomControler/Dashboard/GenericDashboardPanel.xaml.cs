using GrappBox.Model;
using GrappBox.Ressources;
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

// Pour en savoir plus sur le modèle d'élément Contrôle utilisateur, consultez la page http://go.microsoft.com/fwlink/?LinkId=234236

namespace GrappBox.CustomControler
{
    public sealed partial class GenericDashboardPanel : UserControl
    {
        Frame frame = Window.Current.Content as Frame;
        public GenericDashboardPanel()
        {
            this.InitializeComponent();
        }

        private void ListView_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            ListView lv = sender as ListView;
            ProjectListModel plm = lv.SelectedItem as ProjectListModel;
            Debug.WriteLine("ProjectName= {0}  ProjectId= {1}", plm.Name, plm.Id);
            SettingsManager.setOption("ProjectIdChoosen", plm.Id);
            SettingsManager.setOption("ProjectNameChoosen", plm.Name);
            frame.Navigate(typeof(View.DashBoardView));
        }
    }
}
