export type RoadmapStatus = Modules.Roadmap.Enums.RoadmapStatus;

export type RoadmapType = Modules.Roadmap.Enums.RoadmapType;

export interface RoadmapItem {
    id: number;
    title: string;
    description: string | null;
    status: RoadmapStatus;
    status_label: string;
    type: RoadmapType;
    type_label: string;
    net_score: number;
    user_vote: 'up' | 'down' | null;
    created_at: string;
}
