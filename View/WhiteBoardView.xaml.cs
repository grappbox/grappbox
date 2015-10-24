using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Navigation;


namespace GrappBox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class WhiteBoardView : Page
    {
        public WhiteBoardView()
        {
            this.InitializeComponent();
            this.DataContext = new ViewModel.WhiteBoardViewModel();
        }
        /// <summary>
        /// Invoqué lorsque cette page est sur le point d'être affichée dans un frame.
        /// </summary>
        /// <param name="e">Données d’événement décrivant la manière dont l’utilisateur a accédé à cette page.
        /// Ce paramètre est généralement utilisé pour configurer la page.</param>
        protected override void OnNavigatedTo(NavigationEventArgs e)
        {
        }
    }
}