using Grappbox.HttpRequest;
using Grappbox.Model;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using Windows.Web.Http;
using System.Text;
using System.Threading.Tasks;
using Windows.UI.Popups;

namespace Grappbox.ViewModel
{
    class GenericDashboardViewModel : ViewModelBase
    {
        private ObservableCollection<ProjectListModel> _projectList;
        public ObservableCollection<ProjectListModel> ProjectList
        {
            get { return _projectList; }
            set { _projectList = value; NotifyPropertyChanged("ProjectList"); }
        }

        public async Task<bool> getProjectList()
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            object[] token = { User.GetUser().Token };
            HttpResponseMessage res = await api.Get(token, "dashboard/projects");
            if (res == null)
                return false;
            if (res.IsSuccessStatusCode)
            {
                ProjectList = api.DeserializeArrayJson<ObservableCollection<ProjectListModel>>(await res.Content.ReadAsStringAsync());
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
                return false;
            }
            return true;
        }
        public async System.Threading.Tasks.Task getProjectsLogo()
        {
            foreach (ProjectListModel plm in ProjectList)
            {
                await plm.LogoUpdate();
                await plm.SetLogo();
            }
        }
    }
}
