using Newtonsoft.Json.Linq;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using Windows.Web.Http;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI;
using Windows.UI.Core;
using Windows.UI.Popups;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Windows.UI.Xaml.Shapes;
using Grappbox.HttpRequest;
using Grappbox.Resources;
using System.Net.NetworkInformation;
using Windows.Networking.Connectivity;
using Windows.Networking.NetworkOperators;
using Windows.System.Profile;
using Windows.Storage.Streams;
using Windows.Security.Cryptography.Core;
using Windows.Security.Cryptography;
using Grappbox.Utils;

// Pour en savoir plus sur le modèle d'élément Page vierge, consultez la page http://go.microsoft.com/fwlink/?LinkId=391641

namespace Grappbox
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
            LoadingBar.IsEnabled = true;
            LoadingBar.Visibility = Visibility.Visible;

            HttpRequestManager api = HttpRequestManager.Instance;
            Dictionary<string, object> props = new Dictionary<string, object>();
            props.Add("login", loginBlock.Text);
            props.Add("password", pwdBlock.Password);
            props.Add("mac", SystemInformation.GetUniqueIdentifier());
            props.Add("flag", "wph");
            props.Add("device_name", "WindowsPhone");
            HttpResponseMessage res = await api.Post(props, "account/login");
            if (res.IsSuccessStatusCode)
            {
                api.DeserializeJson<User>(await res.Content.ReadAsStringAsync());
                SettingsManager.setOption("login", loginBlock.Text);
                SettingsManager.setOption("password", pwdBlock.Password);
                Debug.WriteLine(User.GetUser().Token);
                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;

//                await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(View.GenericDahsboard)));
            }
            else {
                LoadingBar.IsEnabled = false;
                LoadingBar.Visibility = Visibility.Collapsed;

                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }
    }
}