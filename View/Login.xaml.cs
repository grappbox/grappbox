using Grappbox.CustomControls;
using Grappbox.Helpers;
using Grappbox.HttpRequest;

using System;
using Windows.UI;
using Windows.UI.Core;
using Windows.UI.Popups;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;

// Pour en savoir plus sur le modèle d'élément Page vierge, consultez la page http://go.microsoft.com/fwlink/?LinkId=391641

namespace Grappbox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class LoginPage : Page
    {
        // Constructor
        public LoginPage()
        {
            this.InitializeComponent();
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
            var dialog = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("RedGrappboxBrush"));
            dialog.ShowAsync();
            HttpRequestManager api = HttpRequestManager.Instance;
            bool result = await api.Login(loginBlock.Text, pwdBlock.Password);
            if (result == true)
            {
                dialog.Hide();
                await Dispatcher.RunAsync(CoreDispatcherPriority.Normal, () => this.Frame.Navigate(typeof(View.GenericDahsboard)));
            }
            else
            {
                dialog.Hide();
                MessageDialog msgbox = new MessageDialog("Can't login");
                await msgbox.ShowAsync();
            }
        }
    }
}