using GrappBox.ApiCom;
using GrappBox.Ressources;
using GrappBox.ViewModel;
using Newtonsoft.Json.Linq;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Net.Http;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Windows.UI.Xaml.Shapes;

// Pour en savoir plus sur le modèle d'élément Page vierge, consultez la page http://go.microsoft.com/fwlink/?LinkId=391641

namespace GrappBox
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class MainPage : Page
    {
        // Constructor
        public MainPage()
        {
            this.InitializeComponent();
            this.NavigationCacheMode = NavigationCacheMode.Required;
        }


        /// <summary>
        /// Invoqué lorsque cette page est sur le point d'être affichée dans un frame.
        /// </summary>
        /// <param name="e">Données d’événement décrivant la manière dont l’utilisateur a accédé à cette page.
        /// Ce paramètre est généralement utilisé pour configurer la page.</param>
        protected override void OnNavigatedTo(NavigationEventArgs e)
        { }

        private async void DashBoardButton_Click(object sender, RoutedEventArgs e)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();
            props.Add("login", loginBlock.Text);
            props.Add("password", pwdBlock.Password);
            HttpResponseMessage res = await api.Post(props, "accountadministration/login");
            if (res.IsSuccessStatusCode)
            {
                api.DeserializeJson<User>(await res.Content.ReadAsStringAsync());
                SettingsManager.setOption("login", loginBlock.Text);
                SettingsManager.setOption("password", pwdBlock.Password);
                this.Frame.Navigate(typeof(View.DashBoardView));
            }
            else {
                errorBlock.Text = api.GetErrorMessage(await res.Content.ReadAsStringAsync());
            }
        }
    }
}