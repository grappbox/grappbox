using GrappBox.Model;
using GrappBox.ViewModel;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;

// The User Control item template is documented at http://go.microsoft.com/fwlink/?LinkId=234236

namespace GrappBox.CustomControls
{
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

        public UserRoleCB()
        {
            this.InitializeComponent();
        }

        private async void ComboBox_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            var value = (sender as ComboBox).SelectedValue;
            if (value != null)
            {
                ProjectRoleModel role = await vm.getUserRole((this.DataContext as UserModel).Id);
                int newRole = (int)value;
                if (role.RoleId != newRole)
                {
                    if (role.RoleId == 0 || await vm.removeUserRole(UserId, role.RoleId) == true)
                        await vm.assignUserRole(UserId, newRole);
                }
            }
        }

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