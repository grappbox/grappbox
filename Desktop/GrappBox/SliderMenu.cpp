#include <QException>
#include <QDebug>
#include "SliderMenu.h"

SliderMenu::SliderMenu(QWidget *parent) : QWidget(parent)
{
    _MainLayout = new QVBoxLayout();
    _MainLayout->setAlignment(Qt::AlignTop);
    setLayout(_MainLayout);
    _MainLayout->setMargin(0);
    _MainLayout->setSpacing(0);
    _CurrentIndex = 0;
}

void SliderMenu::AddMenuItem(QString name, int id, bool hided)
{
    QPushButton *newItem = new QPushButton(name);
    newItem->setMaximumHeight(40);
    newItem->setStyleSheet("QPushButton {"
                           "background-color: #f0f3f7;"
                           "border-style: none;"
                           "}");
    newItem->setSizePolicy(QSizePolicy::Expanding, QSizePolicy::Expanding);
    if (hided)
        newItem->hide();
    _ListButton[id] = newItem;
    _MainLayout->addWidget(newItem);
    QObject::connect(newItem, SIGNAL(clicked(bool)), this, SLOT(ButtonChangeMenu()));
}

QString SliderMenu::GetMenuItem(int id)
{
    if (!_ListButton.contains(id))
        throw new QException();
    return _ListButton[id]->text();
}

void SliderMenu::ForceChangeMenu(int menu)
{
    if (!_ListButton.contains(menu))
        throw new QException();
    _CurrentIndex = menu;
    for (QMap<int, QPushButton*>::iterator it = _ListButton.begin(); it != _ListButton.end(); ++it)
    {
        (it.value())->setStyleSheet("QPushButton {"
                               "background-color: #f0f3f7;"
                               "border-style: none;"
                               "}");
    }
    _ListButton[menu]->setStyleSheet("QPushButton {"
                                     "background-color: #f4f6f9;"
                                     "border-style: none;"
                                     "border-right-style: solid;"
                                       "border-width: 4px;"
                                       "border-color: #af2d2e;"
                                     "}");
}

void SliderMenu::ButtonChangeMenu()
{
    QObject *obj = QObject::sender();
    int index = _ListButton.key(dynamic_cast<QPushButton*>(obj));
    qDebug() << "Index button change menu : " << index;
    ForceChangeMenu(index);
    emit MenuChanged(index);
}
