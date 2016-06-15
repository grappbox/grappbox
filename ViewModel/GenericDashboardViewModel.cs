using GrappBox.ApiCom;
using GrappBox.Model;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.ViewModel
{
    class GenericDashboardViewModel : ViewModelBase
    {
        private ObservableCollection<ProjectListModel> _projectList;
        public ObservableCollection<ProjectListModel> ProjectList
        {
            get { return _projectList; }
            set { _projectList = value; NotifyPropertyChanged("ProjectList"); }
        }

        public async System.Threading.Tasks.Task getProjectList()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token };
            HttpResponseMessage res = await api.Get(token, "dashboard/getprojectsglobalprogress");
            if (res.IsSuccessStatusCode)
            {
                Debug.WriteLine(await res.Content.ReadAsStringAsync());
                ProjectList = api.DeserializeArrayJson<ObservableCollection<ProjectListModel>>(await res.Content.ReadAsStringAsync());
            }
            else
            {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
        }
    }
}
