import apiClient from '@/shared/api/client'
import { ANNOUNCEMENT_ENDPOINTS } from '@/shared/api/endpoints'
import type {
  Announcement,
  AnnouncementsResponse,
  CreateAnnouncementData,
  UpdateAnnouncementData,
} from '@/domain/announcements/types'

export const announcementsService = {
  // get published
  async getPublishedAnnouncements(page: number = 1): Promise<AnnouncementsResponse> {
    const response = await apiClient.get<AnnouncementsResponse>(ANNOUNCEMENT_ENDPOINTS.LIST, {
      params: {
        status: 'published',
        page,
      },
    })
    return response.data
  },

  // get my announcements
  async getMyAnnouncements(page: number = 1): Promise<AnnouncementsResponse> {
    const response = await apiClient.get<AnnouncementsResponse>(ANNOUNCEMENT_ENDPOINTS.MY, {
      params: {
        page,
      },
    })
    return response.data
  },

  // get user announcements (admin)
  async getUserAnnouncements(page: number = 1): Promise<AnnouncementsResponse> {
    const response = await apiClient.get<AnnouncementsResponse>(ANNOUNCEMENT_ENDPOINTS.USERS, {
      params: {
        page,
      },
    })
    return response.data
  },

  // get single
  async getAnnouncement(id: number): Promise<Announcement> {
    const response = await apiClient.get<{ announcement: Announcement }>(ANNOUNCEMENT_ENDPOINTS.DETAIL(id))
    return response.data.announcement
  },

  // create
  async createAnnouncement(data: CreateAnnouncementData): Promise<Announcement> {
    const response = await apiClient.post<{ message: string; announcement: Announcement }>(ANNOUNCEMENT_ENDPOINTS.CREATE, data)
    return response.data.announcement
  },

  // update
  async updateAnnouncement(id: number, data: UpdateAnnouncementData): Promise<Announcement> {
    const response = await apiClient.put<{ message: string; announcement: Announcement }>(ANNOUNCEMENT_ENDPOINTS.UPDATE(id), data)
    return response.data.announcement
  },

  // delete
  async deleteAnnouncement(id: number): Promise<void> {
    await apiClient.delete(ANNOUNCEMENT_ENDPOINTS.DELETE(id))
  },
}
