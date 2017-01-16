using Grappbox.Model;
using Grappbox.ViewModel;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;

// The User Control item template is documented at http://go.microsoft.com/fwlink/?LinkId=234236

namespace Grappbox.CustomControls
{
    /// <summary>
    /// User role content dialog
    /// </summary>
    /// <seealso cref="Windows.UI.Xaml.Controls.UserControl" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector2" />
    public sealed partial class UserRoleCB : UserControl
    {
        public static readonly DependencyProperty UserIdProperty =
            DependencyProperty.Register("UserId", typeof(int), typeof(UserRoleCB), null);

        public int UserId
        {
            get { return (int)GetValue(UserIdProperty); }
            set { SetValue(UserIdProperty, value); }
        }

        private ProjectSettingsViewModel vm = ProjectSettingsViewModel.GetViewModel();

        /// <summary>
        /// Initializes a new instance of the <see cref="UserRoleCB"/> class.
        /// </summary>
        public UserRoleCB()
        {
            this.InitializeComponent();
        }

        /// <summary>
        /// Handles the SelectionChanged event of the ComboBox control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="SelectionChangedEventArgs"/> instance containing the event data.</param>
        private async void ComboBox_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            var value = (sender as ComboBox).SelectedValue;
            if (value != null)
            {
                ProjectRoleModel role = await vm.getUserRole((this.DataContext as UsersModel).Id);
                int newRole = (int)value;
                bool success = false;
                if (role.RoleId != newRole)
                {
                    if (role.RoleId == 0 || await vm.removeUserRole(UserId, role.RoleId) == true)
                        success = await vm.assignUserRole(UserId, newRole);
                }
                if (success == false)
                    (sender as ComboBox).SelectedValue = role.RoleId;
            }
        }

        /// <summary>
        /// Handles the Loaded event of the ComboBox control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void ComboBox_Loaded(object sender, RoutedEventArgs e)
        {
            if (UserId != 0)
            {
                ProjectRoleModel role = await vm.getUserRole(UserId);
                if (role != null)
                    (sender as ComboBox).SelectedValue = role.RoleId;
            }
        }
    }
}